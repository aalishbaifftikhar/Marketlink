<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\FarmerDashboardController;
use App\Http\Controllers\FarmerOrderController;
use App\Http\Controllers\FarmerPickupSlotController;
use App\Http\Controllers\FarmerProductController;
use App\Http\Controllers\FarmerStockTemplateController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoriteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

Route::get('/markets', [MarketController::class, 'index'])->name('markets.index');
Route::get('/markets/nearby', [MarketController::class, 'nearby'])->name('markets.nearby');
Route::get('/markets/{market:slug}', [MarketController::class, 'show'])->name('markets.show');
Route::get('/farmers', [FarmerController::class, 'index'])->name('farmers.index');
Route::get('/farmers/{farmer}', [FarmerController::class, 'show'])->name('farmers.show');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

/*
|--------------------------------------------------------------------------
| Guest authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [Auth\RegisteredUserController::class, 'store']);
    Route::get('/login', [Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [Auth\AuthenticatedSessionController::class, 'store']);
    Route::get('/forgot-password', [Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [Auth\NewPasswordController::class, 'store'])->name('password.store');

    // E-mail OTP verification (registration + password reset)
    Route::get('/verify-otp', [Auth\OtpVerificationController::class, 'show'])->name('otp.show');
    Route::post('/verify-otp', [Auth\OtpVerificationController::class, 'verify'])
        ->middleware('throttle:otp-verify')->name('otp.verify');
    Route::post('/verify-otp/resend', [Auth\OtpVerificationController::class, 'resend'])
        ->middleware('throttle:otp-resend')->name('otp.resend');

    // Google OAuth (works for Admin, Customer and Farmer accounts)
    Route::get('/auth/google', [Auth\GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [Auth\GoogleController::class, 'callback'])->name('google.callback');
});

Route::get('/auth/google/complete', [Auth\GoogleController::class, 'showComplete'])->name('google.complete');
Route::post('/auth/google/complete', [Auth\GoogleController::class, 'complete'])->name('google.complete.store');

/*
|--------------------------------------------------------------------------
| Authenticated (any role)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // Favorites (customers)
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

/*
|--------------------------------------------------------------------------
| Customer
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/assistant', [CustomerController::class, 'assistant'])->name('assistant');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [CustomerController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerController::class, 'orderShow'])->name('orders.show');
    Route::get('/orders/{order}/edit', [CustomerController::class, 'orderEdit'])->name('orders.edit');
    Route::put('/orders/{order}', [CustomerController::class, 'orderUpdate'])->name('orders.update');
    Route::post('/orders/{order}/cancel', [CustomerController::class, 'orderCancel'])->name('orders.cancel');
    Route::post('/orders/{order}/reorder', [CustomerController::class, 'orderReorder'])->name('orders.reorder');

    Route::get('/favorites', [CustomerController::class, 'favorites'])->name('favorites');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

/*
|--------------------------------------------------------------------------
| Farmer
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:farmer'])->prefix('farmer')->name('farmer.')->group(function () {
    Route::get('/pending-approval', [FarmerDashboardController::class, 'pending'])->name('pending');

    Route::middleware('farmer.approved')->group(function () {
        Route::get('/dashboard', [FarmerDashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('/profile/edit', [FarmerDashboardController::class, 'profileEdit'])->name('profile.edit');
        Route::put('/profile', [FarmerDashboardController::class, 'profileUpdate'])->name('profile.update');

        Route::get('/products', [FarmerProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [FarmerProductController::class, 'create'])->name('products.create');
        Route::post('/products', [FarmerProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [FarmerProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [FarmerProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [FarmerProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/{product}/toggle-availability', [FarmerProductController::class, 'toggleAvailability'])->name('products.toggle');

        Route::get('/stock-templates', [FarmerStockTemplateController::class, 'index'])->name('templates.index');
        Route::post('/stock-templates', [FarmerStockTemplateController::class, 'store'])->name('templates.store');
        Route::delete('/stock-templates/{stockTemplate}', [FarmerStockTemplateController::class, 'destroy'])->name('templates.destroy');
        Route::post('/stock-templates/{stockTemplate}/apply', [FarmerStockTemplateController::class, 'apply'])->name('templates.apply');

        Route::get('/pickup-slots', [FarmerPickupSlotController::class, 'index'])->name('slots.index');
        Route::post('/pickup-slots', [FarmerPickupSlotController::class, 'store'])->name('slots.store');
        Route::delete('/pickup-slots/{pickupSlot}', [FarmerPickupSlotController::class, 'destroy'])->name('slots.destroy');
        Route::post('/pickup-slots/{pickupSlot}/toggle', [FarmerPickupSlotController::class, 'toggle'])->name('slots.toggle');

        Route::get('/orders', [FarmerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [FarmerOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [FarmerOrderController::class, 'updateStatus'])->name('orders.status');

        Route::get('/insights', [FarmerDashboardController::class, 'insights'])->name('insights');
        Route::get('/insights/entries', [FarmerDashboardController::class, 'entries'])->name('entries.index');
        Route::post('/insights/entries', [FarmerDashboardController::class, 'storeEntry'])->name('entries.store');
        Route::delete('/insights/entries/{entry}', [FarmerDashboardController::class, 'destroyEntry'])->name('entries.destroy');
        Route::get('/reviews', [FarmerDashboardController::class, 'reviews'])->name('reviews.index');
        Route::post('/reviews/{review}/respond', [FarmerDashboardController::class, 'reviewRespond'])->name('reviews.respond');
    });
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/farmers', [Admin\FarmerManagementController::class, 'index'])->name('farmers.index');
    Route::get('/farmers/{farmer}', [Admin\FarmerManagementController::class, 'show'])->name('farmers.show');
    Route::post('/farmers/{farmer}/approve', [Admin\FarmerManagementController::class, 'approve'])->name('farmers.approve');
    Route::post('/farmers/{farmer}/suspend', [Admin\FarmerManagementController::class, 'suspend'])->name('farmers.suspend');
    Route::post('/farmers/{farmer}/restore', [Admin\FarmerManagementController::class, 'restore'])->name('farmers.restore');
    Route::delete('/farmers/{farmer}', [Admin\FarmerManagementController::class, 'destroy'])->name('farmers.destroy');

    Route::get('/customers', [Admin\CustomerManagementController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [Admin\CustomerManagementController::class, 'show'])->name('customers.show');
    Route::post('/customers/{customer}/toggle-status', [Admin\CustomerManagementController::class, 'toggleStatus'])->name('customers.toggle');

    Route::resource('markets', Admin\MarketManagementController::class)->except(['show']);

    Route::get('/products', [Admin\ModerationController::class, 'products'])->name('products.index');
    Route::delete('/products/{product}', [Admin\ModerationController::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/reviews', [Admin\ModerationController::class, 'reviews'])->name('reviews.index');
    Route::post('/reviews/{review}/toggle', [Admin\ModerationController::class, 'toggleReview'])->name('reviews.toggle');

    Route::resource('categories', Admin\CategoryController::class)->except(['show']);

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');

    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [Admin\ReportController::class, 'generate'])->name('reports.generate');
    Route::get('/reports/{report}/download', [Admin\ReportController::class, 'download'])->name('reports.download');

    Route::get('/announcements', [Admin\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [Admin\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [Admin\AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [Admin\AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::get('/settings', [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
});

/*
|--------------------------------------------------------------------------
| AI Assistant (customers; demo works for guests too)
|--------------------------------------------------------------------------
*/
Route::post('/assistant/chat', [AssistantController::class, 'chat'])->middleware('throttle:40,1')->name('assistant.chat');
