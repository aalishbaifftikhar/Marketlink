<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Market;
use App\Models\Order;
use App\Models\PickupSlot;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketLinkCompleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }
    public function test_public_pages_load_successfully(): void
    {
        $this->get('/')->assertStatus(200);
        $this->get('/about')->assertStatus(200);
        $this->get('/contact')->assertStatus(200);
        $this->get('/markets')->assertStatus(200);
        $this->get('/farmers')->assertStatus(200);
        $this->get('/products')->assertStatus(200);
        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
    }

    /**
     * Test user registration as customer.
     */
    public function test_customer_registration_and_access(): void
    {
        $email = 'alice.' . uniqid() . '@example.com';
        $response = $this->post('/register', [
            'name' => 'Alice Test',
            'email' => $email,
            'phone' => '0712345678',
            'address' => '123 Market St',
            'role' => 'customer',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role' => 'customer',
            'status' => 'active',
        ]);
    }

    /**
     * Test user registration as farmer requires approval.
     */
    public function test_farmer_registration_requires_approval(): void
    {
        $email = 'bob.' . uniqid() . '@example.com';
        $response = $this->post('/register', [
            'name' => 'Bob Farmer',
            'email' => $email,
            'phone' => '0798765432',
            'address' => '456 Farm Lane',
            'role' => 'farmer',
            'stall_name' => 'Bob Organic Orchards',
            'contact_person' => 'Bob Farmer',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('farmer.pending'));
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role' => 'farmer',
            'status' => 'pending',
        ]);
    }

    /**
     * Test admin can access admin dashboard and approve pending farmer.
     */
    public function test_admin_dashboard_and_farmer_approval(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        }

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        $pendingFarmer = Farmer::whereHas('user', fn ($q) => $q->where('status', 'pending'))->first();
        if ($pendingFarmer) {
            $approveResp = $this->actingAs($admin)->post("/admin/farmers/{$pendingFarmer->id}/approve");
            $approveResp->assertRedirect();
            $this->assertEquals('active', $pendingFarmer->user->fresh()->status);
        }
    }

    /**
     * Test customer can view cart and AI assistant responds.
     */
    public function test_customer_can_use_cart_and_assistant(): void
    {
        $customer = User::where('role', 'customer')->where('status', 'active')->first();
        $this->assertNotNull($customer);

        $response = $this->actingAs($customer)->get('/customer/cart');
        $response->assertStatus(200);

        $aiResp = $this->actingAs($customer)->postJson('/assistant/chat', [
            'message' => 'What vegetables are available this weekend?',
        ]);
        $aiResp->assertStatus(200)->assertJsonStructure(['reply']);
    }

    /**
     * Test farmer dashboard access for active farmer.
     */
    public function test_active_farmer_dashboard(): void
    {
        $farmer = Farmer::whereHas('user', fn ($q) => $q->where('status', 'active'))->first();
        $this->assertNotNull($farmer);

        $response = $this->actingAs($farmer->user)->get('/farmer/dashboard');
        $response->assertStatus(200);

        $productsResp = $this->actingAs($farmer->user)->get('/farmer/products');
        $productsResp->assertStatus(200);

        $insightsResp = $this->actingAs($farmer->user)->get('/farmer/insights');
        $insightsResp->assertStatus(200);
    }
}
