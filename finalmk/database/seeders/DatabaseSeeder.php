<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\Farmer;
use App\Models\Market;
use App\Models\Order;
use App\Models\PickupSlot;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\StockTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Password123');

        // ---------- Settings ----------
        $settings = [
            'site_name' => 'MarketLink',
            'site_tagline' => 'Fresh from the farm, reserved for you.',
            'contact_email' => 'hello@marketlink.test',
            'contact_phone' => '+254 700 000 000',
            'contact_address' => 'KICC Farmers Market, Nairobi',
            'contact_latitude' => -1.2888,
            'contact_longitude' => 36.8233,
            'footer_text' => 'Connecting local growers with their community.',
            'announcement_banner' => '',
        ];
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // ---------- Users ----------
        // The ONLY admin account in the system. Any other admin row is removed.
        User::where('role', 'admin')->where('email', '!=', User::ADMIN_EMAIL)->delete();

        User::updateOrCreate(
            ['email' => User::ADMIN_EMAIL],
            [
                'name' => 'MarketLink Admin',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $customers = collect(['Amara Njoroge', 'Brian Otieno', 'Chloe Mwangi', 'David Kiprop'])->map(fn ($name) => User::create([
            'name' => $name,
            'email' => Str::snake($name, '.') . '@example.com',
            'password' => $password,
            'role' => 'customer',
            'status' => 'active',
            'phone' => '07' . rand(10000000, 99999999),
            'address' => 'Nairobi',
            'email_verified_at' => now(),
        ]));

        // ---------- Markets ----------
        $marketData = [
            ['KICC Farmers Market', 'Nairobi', -1.2888, 36.8233, ['saturday', 'sunday'], '08:00', '14:00', 'City-centre weekend market with over 40 verified growers.'],
            ['Karen Blixen Market', 'Nairobi', -1.3197, 36.7084, ['saturday'], '09:00', '15:00', 'Leafy suburban market known for organic produce and dairy.'],
            ['Westlands Organic Bazaar', 'Nairobi', -1.2635, 36.8025, ['wednesday', 'saturday'], '08:30', '13:30', 'Midweek and weekend market focusing on certified organic stalls.'],
            ['Ruaka Riverside Market', 'Kiambu', -1.1833, 36.7833, ['sunday'], '08:00', '13:00', 'Riverside Sunday market serving the fast-growing Ruaka community.'],
        ];
        $markets = collect($marketData)->map(fn ($m) => Market::create([
            'name' => $m[0],
            'slug' => Str::slug($m[0]),
            'city' => $m[1],
            'latitude' => $m[2],
            'longitude' => $m[3],
            'operating_days' => $m[4],
            'open_time' => $m[5],
            'close_time' => $m[6],
            'description' => $m[7],
            'address' => $m[0] . ', ' . $m[1],
            'is_active' => true,
        ]));

        // ---------- Categories ----------
        $categoryData = [
            'Vegetables', 'Fruits', 'Dairy & Eggs', 'Meat & Fish',
            'Grains & Cereals', 'Herbs & Spices', 'Honey & Preserves', 'Flowers & Plants',
        ];
        $categories = collect($categoryData)->map(fn ($name, $i) => Category::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Fresh, locally grown ' . strtolower($name) . ' from verified farmers.',
            'sort_order' => $i + 1,
            'is_active' => true,
        ]));

        // ---------- Farmers ----------
        $farmerData = [
            ['Wanjiku Mwangi', 'Green Valley Organics', 'Specialists in certified organic vegetables and herbs grown on a 5-acre plot in Limuru.', ['saturday', 'sunday'], [0, 1]],
            ['Peter Kariuki', 'Kariuki Family Farm', 'Third-generation dairy and poultry farm delivering fresh milk and free-range eggs.', ['saturday', 'wednesday'], [1, 2]],
            ['Achieng Odhiambo', 'Lake Basin Fresh Fish', 'Sustainably sourced tilapia and catfish from Lake Victoria, delivered on ice.', ['saturday', 'sunday'], [0, 3]],
            ['Samuel Ndegwa', 'Ndegwa Grains & Pulses', 'Stone-milled maize flour, beans and lentils from the Nyandarua highlands.', ['sunday'], [0]],
            ['Grace Wambui', 'Wambui Orchards', 'Tree-ripened mangoes, avocados and passion fruit from family orchards in Murang\'a.', ['saturday', 'wednesday'], [2, 0]],
            ['Joseph Maina', 'Maina Bee Keepers', 'Raw honey, beeswax and propolis from hives on the slopes of Mt. Kenya.', ['saturday'], []],
        ];
        $farmers = collect();
        foreach ($farmerData as $i => $fd) {
            $user = User::create([
                'name' => $fd[0],
                'email' => Str::snake($fd[0], '.') . '@example.com',
                'password' => $password,
                'role' => 'farmer',
                'status' => 'active',
                'phone' => '07' . rand(10000000, 99999999),
                'address' => 'Nairobi',
                'email_verified_at' => now(),
            ]);
            $farmer = Farmer::create([
                'user_id' => $user->id,
                'stall_name' => $fd[1],
                'contact_person' => $fd[0],
                'phone' => $user->phone,
                'description' => $fd[2],
                'latitude' => -1.28 + ($i * 0.02),
                'longitude' => 36.80 + ($i * 0.015),
                'operating_days' => $fd[3],
                'order_cutoff_hours' => 24,
            ]);
            $farmer->markets()->attach($fd[4]);
            $farmers->push($farmer);

            // Pickup slots for each operating day.
            foreach ($fd[3] as $day) {
                $dow = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6][$day];
                PickupSlot::create(['farmer_id' => $farmer->id, 'day_of_week' => $dow, 'start_time' => '09:00', 'end_time' => '11:00']);
                PickupSlot::create(['farmer_id' => $farmer->id, 'day_of_week' => $dow, 'start_time' => '11:00', 'end_time' => '13:00']);
            }
        }

        // One farmer still pending approval (admin demo workflow).
        $pendingUser = User::create([
            'name' => 'Faith Njeri',
            'email' => 'faith.njeri@example.com',
            'password' => $password,
            'role' => 'farmer',
            'status' => 'pending',
            'phone' => '0712345678',
            'email_verified_at' => now(),
        ]);
        $pendingFarmer = Farmer::create([
            'user_id' => $pendingUser->id,
            'stall_name' => 'Njeri Hydroponics',
            'contact_person' => 'Faith Njeri',
            'phone' => '0712345678',
            'description' => 'Hydroponic lettuce, kale and herbs grown year-round.',
            'latitude' => -1.30,
            'longitude' => 36.85,
            'operating_days' => ['saturday'],
        ]);

        // ---------- Products ----------
        $productData = [
            [0, 0, 'Organic Sukuma Wiki (Kale)', 60, 'bunch', 40, 'Freshly picked curly kale, no pesticides.'],
            [0, 0, 'Vine Tomatoes', 120, 'kg', 25, 'Ripe vine tomatoes, perfect for salads and stews.'],
            [0, 0, 'Organic Spinach', 55, 'bunch', 35, 'Tender baby spinach leaves.'],
            [0, 0, 'Green Capsicum', 90, 'kg', 20, 'Crunchy green bell peppers.'],
            [0, 5, 'Fresh Coriander (Dania)', 30, 'bunch', 50, 'Fragrant bunches, cut to order.'],
            [0, 5, 'Rosemary Sprigs', 40, 'bunch', 30, 'Pesticide-free rosemary.'],
            [1, 2, 'Fresh Whole Milk', 80, 'litre', 30, 'Raw whole milk from grass-fed Friesian cows.'],
            [1, 2, 'Free-Range Eggs', 180, 'tray (30)', 15, '30 eggs from free-ranging Kienyeji hens.'],
            [1, 2, 'Natural Yoghurt', 150, '500ml', 18, 'Unsweetened, probiotic yoghurt.'],
            [1, 6, 'Farm Butter', 250, '250g', 12, 'Churned from cultured cream.'],
            [2, 3, 'Tilapia (Whole)', 350, 'kg', 20, 'Fresh tilapia, gutted and cleaned.'],
            [2, 3, 'Catfish Fillets', 420, 'kg', 10, 'Boneless catfish fillets.'],
            [2, 3, 'Smoked Nile Perch', 500, 'kg', 8, 'Traditionally smoked over acacia wood.'],
            [3, 4, 'Stone-Milled Maize Flour', 140, '2kg', 40, 'Whole-meal ugali flour, stone-ground weekly.'],
            [3, 4, 'Red Kidney Beans', 160, 'kg', 35, 'Highland red beans, sorted and cleaned.'],
            [3, 4, 'Green Grams (Ndengu)', 180, 'kg', 25, 'Premium green grams.'],
            [3, 4, 'Pure Honey Beans', 155, 'kg', 22, 'Sweet-tasting honey beans.'],
            [4, 1, 'Tree-Ripened Mangoes', 100, 'kg', 30, 'Apple mangoes, ripened on the tree.'],
            [4, 1, 'Hass Avocados', 95, 'kg', 45, 'Buttery Hass avocados.'],
            [4, 1, 'Passion Fruit', 130, 'kg', 28, 'Sweet-purple passion fruit.'],
            [4, 1, 'Sweet Bananas', 80, 'kg', 32, 'Ripe lady-finger bananas.'],
            [5, 6, 'Raw Wild Honey', 400, '500ml', 15, 'Unfiltered, unheated wild honey.'],
            [5, 6, 'Beeswax Blocks', 300, '250g', 10, 'Cleaned beeswax for cosmetics and candles.'],
            [5, 7, 'Potted Mint Plant', 250, 'pot', 14, 'Perennial mint in a 4-inch pot.'],
        ];
        $products = collect();
        foreach ($productData as $pd) {
            $products->push(Product::create([
                'farmer_id' => $farmers[$pd[0]]->id,
                'category_id' => $categories[$pd[1]]->id,
                'name' => $pd[2],
                'description' => $pd[6],
                'price' => $pd[3],
                'unit' => $pd[4],
                'stock_quantity' => $pd[5],
                'is_available' => true,
                'is_active' => true,
            ]));
        }

        // A stock template for the first farmer.
        StockTemplate::create([
            'farmer_id' => $farmers[0]->id,
            'name' => 'Weekly Greens Restock',
            'items' => [
                ['name' => 'Organic Sukuma Wiki (Kale)', 'category_id' => $categories[0]->id, 'price' => 60, 'unit' => 'bunch', 'stock_quantity' => 40, 'description' => ''],
                ['name' => 'Vine Tomatoes', 'category_id' => $categories[0]->id, 'price' => 120, 'unit' => 'kg', 'stock_quantity' => 25, 'description' => ''],
            ],
        ]);

        // ---------- Orders ----------
        $statuses = ['completed', 'completed', 'completed', 'completed', 'completed', 'completed', 'ready_for_pickup', 'ready_for_pickup', 'accepted', 'accepted', 'placed', 'placed', 'placed', 'cancelled', 'declined'];
        $reviewComments = [
            'Amazing quality — the kale stayed fresh all week.',
            'Great produce and friendly pickup. Will order again.',
            'The avocados were perfectly ripe. Highly recommend.',
            'Fresh milk tastes so much better than supermarket stuff.',
            'On time and exactly as described.',
            'Good value for organic produce.',
        ];
        $farmerReplies = ['Asante sana! See you next market day.', 'Thank you for the kind words.', 'We appreciate your support.'];
        $orderCounter = 0;

        foreach ($statuses as $status) {
            $farmer = $farmers->random();
            $customer = $customers->random();
            $slot = PickupSlot::where('farmer_id', $farmer->id)->inRandomOrder()->first();
            if (! $slot) {
                continue;
            }
            $placedAt = Carbon::now()->subDays(rand(0, 29))->subHours(rand(0, 12));
            $pickupDate = $placedAt->copy()->next($slot->dayName() === 'Sunday' ? Carbon::SUNDAY : constant(Carbon::class . '::' . strtoupper($slot->dayName())));

            $items = $products->where('farmer_id', $farmer->id)->random(rand(1, min(3, $products->where('farmer_id', $farmer->id)->count())));
            if ($items->isEmpty()) {
                continue;
            }

            $subtotal = 0;
            $orderItems = [];
            foreach ($items as $product) {
                $qty = rand(1, 3);
                $lineTotal = round($product->price * $qty, 2);
                $subtotal += $lineTotal;
                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'unit_price' => $product->price,
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                ];
                if ($status !== 'cancelled' && $status !== 'declined') {
                    $product->decrement('stock_quantity', $qty);
                }
            }

            $orderCounter++;
            $order = Order::create([
                'order_number' => 'ML-2026-' . str_pad((string) $orderCounter, 5, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'farmer_id' => $farmer->id,
                'market_id' => $farmer->markets()->first()?->id ?? $markets->first()->id,
                'status' => $status,
                'pickup_date' => $pickupDate->toDateString(),
                'pickup_slot' => $slot->label(),
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'customer_notes' => rand(0, 3) === 0 ? 'Please pick the ripest ones, thank you!' : null,
                'cutoff_at' => $pickupDate->copy()->setTimeFromTimeString($slot->start_time)->subHours($farmer->order_cutoff_hours),
                'placed_at' => $placedAt,
                'completed_at' => $status === 'completed' ? $placedAt->copy()->addDays(2) : null,
            ]);
            $order->items()->createMany($orderItems);

            // Reviews for completed orders.
            if ($status === 'completed') {
                Review::create([
                    'user_id' => $customer->id,
                    'farmer_id' => $farmer->id,
                    'product_id' => null,
                    'order_id' => $order->id,
                    'rating' => rand(4, 5),
                    'comment' => $reviewComments[array_rand($reviewComments)],
                    'farmer_response' => rand(0, 1) ? $farmerReplies[array_rand($farmerReplies)] : null,
                    'responded_at' => rand(0, 1) ? now() : null,
                ]);
                $firstItem = $orderItems[0];
                Review::create([
                    'user_id' => $customer->id,
                    'farmer_id' => $farmer->id,
                    'product_id' => $firstItem['product_id'],
                    'order_id' => $order->id,
                    'rating' => rand(4, 5),
                    'comment' => null,
                ]);
            }
        }

        // ---------- Favorites ----------
        foreach ($customers as $customer) {
            foreach ($farmers->random(rand(1, 3)) as $farmer) {
                $customer->toggleFavorite('farmer', $farmer->id);
            }
            foreach ($products->random(rand(2, 5)) as $product) {
                $customer->toggleFavorite('product', $product->id);
            }
        }

        // ---------- Announcements ----------
        Announcement::create([
            'title' => 'Welcome to MarketLink',
            'body' => 'Pre-order from verified local farmers and pick up everything at your community market. Fresh, fair and zero waste.',
            'audience' => 'all',
            'is_published' => true,
            'published_at' => now()->subDays(5),
        ]);
        Announcement::create([
            'title' => 'New: Saturday pickup slots at KICC',
            'body' => 'We have added two new morning pickup slots with several of our most popular farmers. Book early — they fill fast.',
            'audience' => 'customers',
            'is_published' => true,
            'published_at' => now()->subDays(2),
        ]);
        Announcement::create([
            'title' => 'Reminder: update your weekly stock',
            'body' => 'Markets open this weekend. Make sure your availability and pickup slots are up to date so customers can pre-order.',
            'audience' => 'farmers',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
    }
}
