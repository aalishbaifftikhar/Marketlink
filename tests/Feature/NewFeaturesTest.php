<?php

namespace Tests\Feature;

use App\Models\Farmer;
use App\Models\FarmerEntry;
use App\Models\Market;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        Storage::fake('public');
    }

    private function adminUser(): User
    {
        return User::create([
            'name' => 'Test Admin',
            'email' => 'admin.' . uniqid() . '@example.com',
            'password' => 'Password123!',
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    private function approvedFarmer(): User
    {
        $user = User::create([
            'name' => 'Test Farmer',
            'email' => 'farmer.' . uniqid() . '@example.com',
            'password' => 'Password123!',
            'role' => 'farmer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        Farmer::create([
            'user_id' => $user->id,
            'stall_name' => 'Test Stall',
            'contact_person' => 'Test Farmer',
            'phone' => '0700000000',
            'address' => 'Test address',
            'order_cutoff_hours' => 24,
        ]);

        return $user;
    }

    // ======================= Feature 1: Nearby Markets =======================

    public function test_nearby_markets_sorted_by_distance_with_radius_filter(): void
    {
        Market::create(['name' => 'Far Market', 'slug' => 'far-market', 'address' => 'A', 'latitude' => -1.45, 'longitude' => 36.95, 'is_active' => true]);
        Market::create(['name' => 'Close Market', 'slug' => 'close-market', 'address' => 'B', 'latitude' => -1.2922, 'longitude' => 36.8220, 'is_active' => true]);
        Market::create(['name' => 'No Coords Market', 'slug' => 'no-coords', 'address' => 'C', 'is_active' => true]);

        $response = $this->get('/markets/nearby?lat=-1.2921&lng=36.8219&radius=50');

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('Close Market', $content);
        $this->assertStringContainsString('Far Market', $content);
        $this->assertStringContainsString('km away', $content);
        $this->assertStringContainsString("1 other market couldn't be checked", $content);
        $this->assertMatchesRegularExpression('/Close Market.*Far Market/s', $content);
    }

    public function test_nearby_markets_excluded_beyond_radius(): void
    {
        Market::create(['name' => 'Far Market', 'slug' => 'far-market', 'address' => 'A', 'latitude' => -1.45, 'longitude' => 36.95, 'is_active' => true]);

        $this->get('/markets/nearby?lat=-1.2921&lng=36.8219&radius=5')
            ->assertStatus(200)
            ->assertSee('No markets within 5 km');
    }

    public function test_nearby_page_without_location_shows_prompt(): void
    {
        $this->get('/markets/nearby')
            ->assertStatus(200)
            ->assertSee('Use my location')
            ->assertDontSee('km away');
    }

    // ======================= Feature 2: Multiple images =======================

    public function test_admin_market_store_with_multiple_images(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post('/admin/markets', [
            'name' => 'Gallery Market',
            'address' => '1 Test St',
            'city' => 'Testville',
            'images' => [
                UploadedFile::fake()->image('one.png'),
                UploadedFile::fake()->image('two.jpg'),
                UploadedFile::fake()->image('three.webp'),
            ],
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/markets');
        $market = Market::where('slug', 'gallery-market')->first();
        $this->assertNotNull($market);
        $this->assertCount(3, $market->images);
        $this->assertSame($market->images[0], $market->image);
        foreach ($market->images as $path) {
            Storage::disk('public')->assertExists($path);
        }

        $this->actingAs($admin)->get('/markets/gallery-market')
            ->assertStatus(200)
            ->assertSee('Photos')
            ->assertSee('storage/' . $market->images[0])
            ->assertSee('storage/' . $market->images[1]);
    }

    public function test_admin_market_store_blocks_zero_images(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->post('/admin/markets', [
            'name' => 'No Image Market',
            'address' => '1 Test St',
            'city' => 'Testville',
            'is_active' => '1',
        ])->assertSessionHasErrors('images');

        $this->assertDatabaseMissing('markets', ['slug' => 'no-image-market']);
    }

    public function test_admin_market_store_blocks_more_than_six_images(): void
    {
        $admin = $this->adminUser();

        $files = [];
        foreach (range(1, 7) as $i) {
            $files[] = UploadedFile::fake()->image("img$i.png");
        }

        $this->actingAs($admin)->post('/admin/markets', [
            'name' => 'Too Many Market',
            'address' => '1 Test St',
            'city' => 'Testville',
            'images' => $files,
            'is_active' => '1',
        ])->assertSessionHasErrors('images');

        $this->assertDatabaseMissing('markets', ['slug' => 'too-many-market']);
    }

    public function test_admin_market_store_blocks_invalid_file_type_and_oversize(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->post('/admin/markets', [
            'name' => 'Bad Type Market',
            'address' => '1 Test St',
            'city' => 'Testville',
            'images' => [UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')],
            'is_active' => '1',
        ])->assertSessionHasErrors('images.0');

        $this->actingAs($admin)->post('/admin/markets', [
            'name' => 'Huge Image Market',
            'address' => '1 Test St',
            'city' => 'Testville',
            'images' => [UploadedFile::fake()->image('huge.png')->size(6000)],
            'is_active' => '1',
        ])->assertSessionHasErrors('images.0');
    }

    public function test_admin_market_edit_removes_and_adds_images(): void
    {
        $admin = $this->adminUser();
        $market = Market::create(['name' => 'Edit Market', 'slug' => 'edit-market', 'address' => 'A', 'is_active' => true]);
        $oldPaths = [];
        foreach (range(1, 2) as $i) {
            $oldPaths[] = UploadedFile::fake()->image("old$i.png")->store('markets', 'public');
        }
        $market->update(['images' => $oldPaths, 'image' => $oldPaths[0]]);

        $response = $this->actingAs($admin)->put("/admin/markets/{$market->id}", [
            'name' => 'Edit Market',
            'address' => 'A',
            'city' => 'Testville',
            'images' => [UploadedFile::fake()->image('new.png')],
            'remove_images' => [$oldPaths[0]],
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/markets');
        $market->refresh();
        $this->assertSame([$oldPaths[1]], array_slice($market->images, 0, 1));
        $this->assertCount(2, $market->images);
        Storage::disk('public')->assertMissing($oldPaths[0]);
        Storage::disk('public')->assertExists($oldPaths[1]);
        $this->assertSame($market->images[0], $market->image);
    }

    public function test_farmer_product_store_and_update_with_gallery(): void
    {
        $user = $this->approvedFarmer();

        $this->actingAs($user)->post('/farmer/products', [
            'name' => 'Test Tomatoes',
            'price' => '2.50',
            'unit' => 'kg',
            'stock_quantity' => '10',
            'images' => [
                UploadedFile::fake()->image('a.png'),
                UploadedFile::fake()->image('b.png'),
            ],
            'is_available' => '1',
            'is_active' => '1',
        ])->assertRedirect('/farmer/products');

        $product = Product::where('name', 'Test Tomatoes')->first();
        $this->assertNotNull($product);
        $this->assertCount(2, $product->images);
        $this->assertSame($product->images[0], $product->image);

        // Detail page shows the gallery slider.
        $this->get('/products/' . $product->id)
            ->assertStatus(200)
            ->assertSee('storage/' . $product->images[0])
            ->assertSee('storage/' . $product->images[1]);

        // Remove one image, add another.
        $oldSecond = $product->images[1];
        $this->actingAs($user)->put("/farmer/products/{$product->id}", [
            'name' => 'Test Tomatoes',
            'price' => '2.50',
            'unit' => 'kg',
            'stock_quantity' => '10',
            'images' => [UploadedFile::fake()->image('c.png')],
            'remove_images' => [$oldSecond],
            'is_available' => '1',
            'is_active' => '1',
        ])->assertRedirect('/farmer/products');

        $product->refresh();
        $this->assertCount(2, $product->images);
        $this->assertNotContains($oldSecond, $product->images);
        Storage::disk('public')->assertMissing($oldSecond);
    }

    public function test_farmer_product_blocks_zero_images(): void
    {
        $user = $this->approvedFarmer();

        $this->actingAs($user)->post('/farmer/products', [
            'name' => 'No Photo Product',
            'price' => '1.00',
            'unit' => 'kg',
            'stock_quantity' => '5',
            'is_available' => '1',
            'is_active' => '1',
        ])->assertSessionHasErrors('images');

        $this->assertDatabaseMissing('products', ['name' => 'No Photo Product']);
    }

    public function test_legacy_single_image_still_works_in_gallery(): void
    {
        $user = $this->approvedFarmer();
        $legacyPath = UploadedFile::fake()->image('legacy.png')->store('products', 'public');
        $product = Product::create([
            'farmer_id' => $user->farmer->id,
            'name' => 'Legacy Product',
            'slug' => 'legacy-product',
            'price' => 1,
            'unit' => 'kg',
            'stock_quantity' => 1,
            'image' => $legacyPath,
        ]);

        $this->assertSame([$legacyPath], $product->galleryPaths());
        $this->get('/products/' . $product->id)->assertStatus(200);
    }

    // ======================= Feature 3: Farmer insights entries =======================

    public function test_farmer_can_add_entry_and_see_totals(): void
    {
        $user = $this->approvedFarmer();

        $this->actingAs($user)->post('/farmer/insights/entries', [
            'product_name' => 'Tomatoes',
            'quantity' => '10',
            'cost_price' => '2.00',
            'selling_price' => '3.00',
            'entry_date' => now()->toDateString(),
        ])->assertRedirect();

        $entry = FarmerEntry::where('product_name', 'Tomatoes')->first();
        $this->assertNotNull($entry);
        $this->assertSame(20.0, $entry->totalCost());
        $this->assertSame(30.0, $entry->totalSales());
        $this->assertSame(10.0, $entry->profit());

        $response = $this->actingAs($user)->get('/farmer/insights/entries');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('$20.00', $content);
        $this->assertStringContainsString('$30.00', $content);
        $this->assertStringContainsString('+$10.00', $content);
        $this->assertStringContainsString('text-green-600', $content);
        $this->assertStringContainsString('Profit &amp; Loss', $content);
    }

    public function test_loss_is_shown_in_red(): void
    {
        $user = $this->approvedFarmer();
        $user->farmer->entries()->create([
            'product_name' => 'Kales',
            'quantity' => 5,
            'cost_price' => 4,
            'selling_price' => 2,
            'entry_date' => now()->toDateString(),
        ]);

        $content = $this->actingAs($user)->get('/farmer/insights/entries')->getContent();
        $this->assertStringContainsString('−$10.00', $content);
        $this->assertStringContainsString('text-red-600', $content);
    }

    public function test_entries_filter_by_month_and_custom_range(): void
    {
        $user = $this->approvedFarmer();
        $farmer = $user->farmer;

        $farmer->entries()->create(['product_name' => 'June Item', 'quantity' => 1, 'cost_price' => 1, 'selling_price' => 2, 'entry_date' => '2026-06-15']);
        $farmer->entries()->create(['product_name' => 'May Item', 'quantity' => 1, 'cost_price' => 1, 'selling_price' => 2, 'entry_date' => '2026-05-10']);

        $this->actingAs($user)->get('/farmer/insights/entries?mode=month&month=6&year=2026')
            ->assertStatus(200)
            ->assertSee('June Item')
            ->assertDontSee('May Item');

        $this->actingAs($user)->get('/farmer/insights/entries?mode=range&from=2026-05-01&to=2026-05-31')
            ->assertStatus(200)
            ->assertSee('May Item')
            ->assertDontSee('June Item');
    }

    public function test_farmer_cannot_delete_another_farmers_entry(): void
    {
        $owner = $this->approvedFarmer();
        $other = $this->approvedFarmer();
        $entry = $owner->farmer->entries()->create([
            'product_name' => 'Mine', 'quantity' => 1, 'cost_price' => 1, 'selling_price' => 2, 'entry_date' => now(),
        ]);

        $this->actingAs($other)->delete("/farmer/insights/entries/{$entry->id}")->assertStatus(403);
        $this->assertDatabaseHas('farmer_entries', ['id' => $entry->id]);

        $this->actingAs($owner)->delete("/farmer/insights/entries/{$entry->id}")->assertRedirect();
        $this->assertDatabaseMissing('farmer_entries', ['id' => $entry->id]);
    }
}
