<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\CheckoutController;
use App\Http\Middleware\ResolveTenant;

// ====================================================
// JALUR 1: SISTEM ADMIN & DASHBOARD (Prioritas Utama)
// Hanya bisa diakses dari localhost, 127.0.0.1, atau domain utama aplikasi
// ====================================================

// Tentukan domain yang dianggap sebagai "Sistem Pusat"
$systemDomains = ['localhost', '127.0.0.1'];
if (env('APP_URL')) {
    $host = parse_url(env('APP_URL'), PHP_URL_HOST);
    if ($host) $systemDomains[] = $host;
}
$systemDomains = array_unique($systemDomains);

foreach ($systemDomains as $domain) {
    Route::domain($domain)->group(function () {
        
        // 1. Halaman Depan Sistem -> Redirect
        Route::get('/', function () {
            if (Auth::check()) {
                if (auth()->user()->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                }
                return redirect()->route('client.websites');
            }
            return redirect()->route('login');
        })->name('home');

        // 2. Auth Routes (Login, Register, Logout)
        Auth::routes();

        // 3. Profile Routes
        Route::middleware(['auth'])->group(function () {
            Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
        });

        // 4. Dashboard Admin & Client (Wajib Login)
        Route::middleware(['auth'])->group(function() {
            
            // --- Super Admin Routes ---
            Route::prefix('admin')
                ->name('admin.')
                ->middleware('admin') // Pastikan middleware ini terdaftar di Kernel
                ->group(function () {
                    // Dashboard
                    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
                    
                    // Resources
                    Route::resource('packages', App\Http\Controllers\Admin\PackageController::class);
                    
                    // Transactions
                    Route::get('/transactions', [App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');
                    Route::put('/transactions/{transaction}', [App\Http\Controllers\Admin\TransactionController::class, 'update'])->name('transactions.update');
                    
                    // Websites & Users
                    Route::get('/websites', [App\Http\Controllers\Admin\WebsiteController::class, 'index'])->name('websites.index');
                    Route::delete('/websites/{website}', [App\Http\Controllers\Admin\WebsiteController::class, 'destroy'])->name('websites.destroy');
                    
                    Route::get('/users/{id}/impersonate', [App\Http\Controllers\Admin\UserController::class, 'impersonate'])->name('users.impersonate');
                    Route::delete('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
            });

            // --- Client Dashboard (Pemilik Toko) ---
            
            // Halaman Pilih Website
            Route::get('/select-website', [App\Http\Controllers\WebsiteController::class, 'index'])->name('client.websites');
            Route::post('/websites', [App\Http\Controllers\WebsiteController::class, 'store'])->name('client.websites.store');

            // Dashboard Toko Tertentu
            Route::prefix('manage/{website}')
                ->middleware([App\Http\Middleware\CheckSubscription::class]) // Pastikan middleware ini aman
                ->group(function () {
                    
                    // Dashboard Utama
                    Route::get('/dashboard', [App\Http\Controllers\Client\DashboardController::class, 'index'])->name('client.dashboard');

                    // Produk
                    Route::get('/products', [App\Http\Controllers\Client\ProductController::class, 'index'])->name('client.products.index');
                    Route::get('/products/create', [App\Http\Controllers\Client\ProductController::class, 'create'])->name('client.products.create');
                    Route::post('/products', [App\Http\Controllers\Client\ProductController::class, 'store'])->name('client.products.store');
                    Route::get('/products/{product}/edit', [App\Http\Controllers\Client\ProductController::class, 'edit'])->name('client.products.edit');
                    Route::put('/products/{product}', [App\Http\Controllers\Client\ProductController::class, 'update'])->name('client.products.update');
                    Route::delete('/products/{product}', [App\Http\Controllers\Client\ProductController::class, 'destroy'])->name('client.products.destroy');

                    // Kategori
                    Route::get('/categories', [App\Http\Controllers\Client\CategoryController::class, 'index'])->name('client.categories.index');
                    Route::post('/categories', [App\Http\Controllers\Client\CategoryController::class, 'store'])->name('client.categories.store');
                    Route::get('/categories/{category}/edit', [App\Http\Controllers\Client\CategoryController::class, 'edit'])->name('client.categories.edit');
                    Route::put('/categories/{category}', [App\Http\Controllers\Client\CategoryController::class, 'update'])->name('client.categories.update');
                    Route::delete('/categories/{category}', [App\Http\Controllers\Client\CategoryController::class, 'destroy'])->name('client.categories.destroy');

                    // Orders
                    Route::get('/orders', [App\Http\Controllers\Client\OrderController::class, 'index'])->name('client.orders.index');
                    Route::get('/orders/{order}', [App\Http\Controllers\Client\OrderController::class, 'show'])->name('client.orders.show');
                    Route::put('/orders/{order}', [App\Http\Controllers\Client\OrderController::class, 'update'])->name('client.orders.update');

                    // Blog Posts
                    Route::get('/posts', [App\Http\Controllers\Client\PostController::class, 'index'])->name('client.posts.index');
                    Route::get('/posts/create', [App\Http\Controllers\Client\PostController::class, 'create'])->name('client.posts.create');
                    Route::post('/posts', [App\Http\Controllers\Client\PostController::class, 'store'])->name('client.posts.store');
                    Route::get('/posts/{post}/edit', [App\Http\Controllers\Client\PostController::class, 'edit'])->name('client.posts.edit');
                    Route::put('/posts/{post}', [App\Http\Controllers\Client\PostController::class, 'update'])->name('client.posts.update');
                    Route::delete('/posts/{post}', [App\Http\Controllers\Client\PostController::class, 'destroy'])->name('client.posts.destroy');

                    // Settings & Domain
                    Route::get('/settings', [App\Http\Controllers\Client\SettingController::class, 'index'])->name('client.settings.index');
                    Route::put('/settings', [App\Http\Controllers\Client\SettingController::class, 'update'])->name('client.settings.update');
                    
                    Route::get('/domain', [App\Http\Controllers\Client\DomainController::class, 'index'])->name('client.domains.index');
                    Route::post('/domain', [App\Http\Controllers\Client\DomainController::class, 'update'])->name('client.domains.update'); // Perhatikan method POST/PUT sesuai controller
                    Route::delete('/domain', [App\Http\Controllers\Client\DomainController::class, 'destroy'])->name('client.domains.destroy');

                    // Lain-lain (Builder, SEO, Appearance, dll)
                    Route::get('/builder', [App\Http\Controllers\Client\BuilderController::class, 'index'])->name('client.builder.index');
                    Route::put('/builder', [App\Http\Controllers\Client\BuilderController::class, 'update'])->name('client.builder.update');
                    
                    Route::get('/appearance', [App\Http\Controllers\Client\AppearanceController::class, 'index'])->name('client.appearance.index');
                    Route::put('/appearance', [App\Http\Controllers\Client\AppearanceController::class, 'update'])->name('client.appearance.update');

                    Route::get('/seo', [App\Http\Controllers\Client\SeoController::class, 'index'])->name('client.seo.index');
                    Route::put('/seo', [App\Http\Controllers\Client\SeoController::class, 'update'])->name('client.seo.update');
                    
                    Route::get('/billing', [App\Http\Controllers\Client\BillingController::class, 'index'])->name('client.billing.index');
                    Route::post('/billing', [App\Http\Controllers\Client\BillingController::class, 'store'])->name('client.billing.store');
                    
                    Route::get('/customers', [App\Http\Controllers\Client\CustomerController::class, 'index'])->name('client.customers.index');
                    Route::get('/reports', [App\Http\Controllers\Client\ReportController::class, 'index'])->name('client.reports.index');
                    Route::get('/templates', [App\Http\Controllers\Client\TemplateController::class, 'index'])->name('client.templates.index');
                    Route::put('/templates', [App\Http\Controllers\Client\TemplateController::class, 'update'])->name('client.templates.update');
            });
        });
    });
}


// ====================================================
// JALUR 2: TOKO ONLINE / STOREFRONT (Catch-All)
// Menangkap semua domain SISANYA (misal: elecjos.com)
// yang TIDAK masuk ke dalam $systemDomains di atas.
// ====================================================

Route::middleware([ResolveTenant::class])->group(function () {
    
    // Homepage Toko
    Route::get('/', [StorefrontController::class, 'index'])->name('store.home');
    
    // Blog Toko
    Route::get('/blog', [StorefrontController::class, 'blogIndex'])->name('store.blog');
    Route::get('/blog/{slug}', [StorefrontController::class, 'blogShow'])->name('store.blog.show');

    // Cart & Checkout
    Route::get('/cart', [CheckoutController::class, 'cart'])->name('store.cart');
    Route::post('/cart/add/{id}', [CheckoutController::class, 'addToCart'])->name('store.cart.add');
    Route::patch('/cart/update', [CheckoutController::class, 'updateCart'])->name('store.cart.update'); // Sesuaikan method di controller (patch/post)
    Route::delete('/cart/remove/{id}', [CheckoutController::class, 'removeFromCart'])->name('store.cart.remove');
    
    // Checkout with throttling
    Route::middleware(['throttle:5,1'])->group(function () {
        Route::post('/checkout', [CheckoutController::class, 'processCheckout'])->name('store.checkout');
    });
    
    // DETAIL PRODUK (PENTING: Taruh paling bawah agar tidak memakan route lain)
    // Route::get('/{slug}', [StorefrontController::class, 'product'])->name('store.product'); 
});