<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WebsiteController; // <--- PENTING: Import Controller
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\ProductController;

// Route::get('/', function () {
//     // 1. Cek apakah user login?
//     if (Auth::check()) {
        
//         // 2. Cek apakah dia ADMIN?
//         if (auth()->user()->role === 'admin') {
//             return redirect()->route('admin.dashboard'); // Arahkan ke Kantor Pusat
//         }

//         // 3. Jika bukan admin, berarti CLIENT
//         return redirect()->route('client.websites'); // Arahkan ke Toko
//     }

//     // 4. Jika belum login, ke halaman Login
//     return redirect()->route('login');
// });

Route::get('/', [App\Http\Controllers\LandingController::class, 'index'])->name('landing');

Auth::routes();

// Group Middleware: User harus login dulu
Route::middleware(['auth'])->group(function () {

    // --- FITUR PROFILE (BARU) ---
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    
    // Halaman List Website
    Route::get('/select-website', [WebsiteController::class, 'index'])->name('client.websites');
    
    // Aksi Buat Website
    Route::post('/websites', [WebsiteController::class, 'store'])->name('client.websites.store');

    // 3. Dashboard Admin Toko (CMS)
    Route::prefix('manage/{website}')->group(function () {
        
        // Dashboard Utama
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('client.dashboard');

        // Nanti kita tambah route lain disini (Produk, Order, dll)
        // ... di dalam group manage/{website} ...

        // Dashboard Utama
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('client.dashboard');

        // --- FITUR PRODUK ---
        Route::get('/products', [ProductController::class, 'index'])->name('client.products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('client.products.create');

        Route::post('/products', [ProductController::class, 'store'])->name('client.products.store');
    // Nanti kita tambah route POST store, edit, delete disini

        // ... route produk yang sudah ada ...

    // --- FITUR KATEGORI ---
        Route::get('/categories', [App\Http\Controllers\Client\CategoryController::class, 'index'])->name('client.categories.index');
        Route::post('/categories', [App\Http\Controllers\Client\CategoryController::class, 'store'])->name('client.categories.store');
        Route::delete('/categories/{category}', [App\Http\Controllers\Client\CategoryController::class, 'destroy'])->name('client.categories.destroy');


        // ... rute produk create & store yang sudah ada ...

        // --- TAMBAHKAN INI UNTUK EDIT & DELETE ---
        
        // 1. Tampilkan Form Edit
        Route::get('/products/{product}/edit', [App\Http\Controllers\Client\ProductController::class, 'edit'])->name('client.products.edit');
        
        // 2. Proses Update Data (PUT)
        Route::put('/products/{product}', [App\Http\Controllers\Client\ProductController::class, 'update'])->name('client.products.update');
        
        // 3. Proses Hapus Data (DELETE)
        Route::delete('/products/{product}', [App\Http\Controllers\Client\ProductController::class, 'destroy'])->name('client.products.destroy');

        // ... route produk & kategori ...

        // --- FITUR WEBSITE BUILDER ---
        Route::get('/builder', [App\Http\Controllers\Client\BuilderController::class, 'index'])->name('client.builder.index');
        Route::put('/builder', [App\Http\Controllers\Client\BuilderController::class, 'update'])->name('client.builder.update');
        // ... route builder & produk ...

    // --- FITUR ORDER (PENJUALAN) ---
        Route::get('/orders', [App\Http\Controllers\Client\OrderController::class, 'index'])->name('client.orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\Client\OrderController::class, 'show'])->name('client.orders.show');
        Route::put('/orders/{order}', [App\Http\Controllers\Client\OrderController::class, 'update'])->name('client.orders.update');

        // ... route order dll ...

        // --- PENGATURAN TOKO ---
        Route::get('/settings', [App\Http\Controllers\Client\SettingController::class, 'index'])->name('client.settings.index');
        Route::put('/settings', [App\Http\Controllers\Client\SettingController::class, 'update'])->name('client.settings.update');

        // ... route settings ...

        // --- FITUR PELANGGAN ---
        Route::get('/customers', [App\Http\Controllers\Client\CustomerController::class, 'index'])->name('client.customers.index');

        // ... route customers ...

        // --- FITUR LAPORAN ---
        Route::get('/reports', [App\Http\Controllers\Client\ReportController::class, 'index'])->name('client.reports.index');

        // ... route produk/kategori ...

        // --- FITUR BLOG ---
        Route::get('/posts', [App\Http\Controllers\Client\PostController::class, 'index'])->name('client.posts.index');
        Route::get('/posts/create', [App\Http\Controllers\Client\PostController::class, 'create'])->name('client.posts.create');
        Route::post('/posts', [App\Http\Controllers\Client\PostController::class, 'store'])->name('client.posts.store');
        Route::delete('/posts/{post}', [App\Http\Controllers\Client\PostController::class, 'destroy'])->name('client.posts.destroy');
        // ... route posts lainnya ...
        Route::get('/posts/{post}/edit', [App\Http\Controllers\Client\PostController::class, 'edit'])->name('client.posts.edit');
        Route::put('/posts/{post}', [App\Http\Controllers\Client\PostController::class, 'update'])->name('client.posts.update');

        // --- FITUR TEMPLATE ---
        Route::get('/templates', [App\Http\Controllers\Client\TemplateController::class, 'index'])->name('client.templates.index');
        Route::put('/templates', [App\Http\Controllers\Client\TemplateController::class, 'update'])->name('client.templates.update');

            // --- FITUR DOMAIN ---
        Route::get('/domain', [App\Http\Controllers\Client\DomainController::class, 'index'])->name('client.domains.index');
        Route::post('/domain', [App\Http\Controllers\Client\DomainController::class, 'update'])->name('client.domains.update');
        Route::delete('/domain', [App\Http\Controllers\Client\DomainController::class, 'destroy'])->name('client.domains.destroy');

            // --- FITUR SEO ---
        Route::get('/seo', [App\Http\Controllers\Client\SeoController::class, 'index'])->name('client.seo.index');
        Route::put('/seo', [App\Http\Controllers\Client\SeoController::class, 'update'])->name('client.seo.update');

        // ... route seo ...

        // --- FITUR MENU / NAVIGASI (Appearance) ---
        Route::get('/appearance', [App\Http\Controllers\Client\AppearanceController::class, 'index'])->name('client.appearance.index');
        Route::put('/appearance', [App\Http\Controllers\Client\AppearanceController::class, 'update'])->name('client.appearance.update');

                // --- FITUR BILLING ---
        Route::get('/billing', [App\Http\Controllers\Client\BillingController::class, 'index'])->name('client.billing.index');
        Route::post('/billing', [App\Http\Controllers\Client\BillingController::class, 'store'])->name('client.billing.store');
        });

});

// --- RUTE UNTUK MELIHAT TOKO (STOREFRONT) ---

// Cara Akses: http://127.0.0.1:8000/s/{subdomain}
// Contoh: http://127.0.0.1:8000/s/tokoelektronik
Route::get('/s/{subdomain}', [App\Http\Controllers\StorefrontController::class, 'index'])->name('store.home');

// ... di bawah route store.home ...

// Tambah ke Keranjang
Route::post('/s/{subdomain}/cart/add/{id}', [App\Http\Controllers\CheckoutController::class, 'addToCart'])->name('store.cart.add');

// Lihat Keranjang
Route::get('/s/{subdomain}/cart', [App\Http\Controllers\CheckoutController::class, 'cart'])->name('store.cart');
// ... route cart sebelumnya ...

// Update Qty
Route::patch('/s/{subdomain}/cart/update', [App\Http\Controllers\CheckoutController::class, 'updateCart'])->name('store.cart.update');

// Hapus Item
Route::delete('/s/{subdomain}/cart/remove/{id}', [App\Http\Controllers\CheckoutController::class, 'removeFromCart'])->name('store.cart.remove');

// ... di group Storefront ...

// Proses Checkout (Simpan ke DB)
Route::post('/s/{subdomain}/checkout', [App\Http\Controllers\CheckoutController::class, 'processCheckout'])->name('store.checkout');

// Di bagian Storefront (Paling Bawah)
Route::get('/s/{subdomain}/blog', [App\Http\Controllers\StorefrontController::class, 'blogIndex'])->name('store.blog');
Route::get('/s/{subdomain}/blog/{slug}', [App\Http\Controllers\StorefrontController::class, 'blogShow'])->name('store.blog.show');

// --- GRUP ROUTE SUPER ADMIN ---
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin']) // <--- INI PAGARNYA
    ->group(function () {
        
        // Dashboard Pusat
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        // Nanti kita tambah route Paket & User disini...
        // ... di dalam Route::prefix('admin')->group(...) ...

        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Manajemen Paket (Resource Route ringkas)
        Route::resource('packages', App\Http\Controllers\Admin\PackageController::class)->only(['index', 'edit', 'update']);

        // ... di dalam grup admin ...
    
        // Manajemen Transaksi
        Route::get('/transactions', [App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');
        Route::put('/transactions/{transaction}', [App\Http\Controllers\Admin\TransactionController::class, 'update'])->name('transactions.update');
        // ... route transaksi ...
    
        // Manajemen Website
        Route::get('/websites', [App\Http\Controllers\Admin\WebsiteController::class, 'index'])->name('websites.index');
        Route::delete('/websites/{website}', [App\Http\Controllers\Admin\WebsiteController::class, 'destroy'])->name('websites.destroy');
    });