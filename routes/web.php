<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WebsiteController; // <--- PENTING: Import Controller
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Middleware\ResolveTenant;
use App\Models\Website;
use Illuminate\Support\Facades\Session;

Route::get('/cek-config', function () {
    $url = config('app.url');
    $domain = config('session.domain');
    
    // Kita bungkus dengan tanda kutip untuk melihat spasi/enter tersembunyi
    return [
        'APP_URL_RAW' => json_encode($url), // json_encode akan menampilkan \n atau \r jika ada
        'SESSION_DOMAIN_RAW' => json_encode($domain),
        'Panjang_URL' => strlen($url),
        'Panjang_Domain' => strlen($domain),
    ];
});
Route::get('/debug-auth', function () {
    return [
        'is_logged_in' => Auth::check(),
        'user' => Auth::user(),
        'session_id' => session()->getId(),
        'cookie' => request()->cookie(config('session.cookie')),
        'website_in_session' => session('website_id') ? Website::find(session('website_id')) : null,
    ];
});

Route::get('/preview/{website}', [App\Http\Controllers\StorefrontController::class, 'preview'])
    ->name('website.preview');

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('client.websites');
    }
    return redirect()->route('login');
})->name('home');

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
    Route::prefix('manage/{website}')
    ->middleware(['auth', App\Http\Middleware\CheckSubscription::class])
    ->group(function () {
       
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
        Route::get('/categories/{category}/edit', [App\Http\Controllers\Client\CategoryController::class, 'edit'])->name('client.categories.edit');
        Route::put('/categories/{category}', [App\Http\Controllers\Client\CategoryController::class, 'update'])->name('client.categories.update');
       
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
        // --- FITUR MENU / NAVIGASI (Appearance) ---
        Route::get('/appearance', [App\Http\Controllers\Client\AppearanceController::class, 'index'])->name('client.appearance.index');
        Route::put('/appearance', [App\Http\Controllers\Client\AppearanceController::class, 'update'])->name('client.appearance.update');
                // --- FITUR BILLING ---
        Route::get('/billing', [App\Http\Controllers\Client\BillingController::class, 'index'])->name('client.billing.index');
        Route::post('/billing', [App\Http\Controllers\Client\BillingController::class, 'store'])->name('client.billing.store');

        // ... di dalam group manage/{website} ...

        // SHIPPING RATES ROUTES
        
        Route::get('/shipping', [App\Http\Controllers\Client\ShippingController::class, 'index'])->name('client.shipping.index');

        
        Route::post('/shipping', [App\Http\Controllers\Client\ShippingController::class, 'store'])->name('client.shipping.store');

        Route::delete('/shipping/clear', [App\Http\Controllers\Client\ShippingController::class, 'clear'])->name('client.shipping.clear');

        Route::delete('/shipping/{rate}', [App\Http\Controllers\Client\ShippingController::class, 'destroy'])->name('client.shipping.destroy');

        // IMPORT & TEMPLATE
        Route::get('/shipping/template', [App\Http\Controllers\Client\ShippingController::class, 'downloadTemplate'])->name('client.shipping.template');
        Route::post('/shipping/import', [App\Http\Controllers\Client\ShippingController::class, 'import'])->name('client.shipping.import');
                // Route untuk Hapus Semua Data Ongkir
        
        });
});
// --- RUTE UNTUK MELIHAT TOKO (STOREFRONT) ---

// GANTI DARI 'domain' KE 'prefix'
Route::group(['prefix' => 's/{subdomain}', 'middleware' => ['web', ResolveTenant::class]], function () {
    Route::get('/', [App\Http\Controllers\StorefrontController::class, 'index'])->name('store.home');
    Route::get('/product/{slug}', [App\Http\Controllers\StorefrontController::class, 'product'])->name('store.product');

    // ... Cart Routes (Pastikan controller menerima parameter $subdomain) ...
    Route::post('/cart/add/{id}', [App\Http\Controllers\CheckoutController::class, 'addToCart'])->name('store.cart.add');
    Route::get('/cart', [App\Http\Controllers\CheckoutController::class, 'cart'])->name('store.cart');
    Route::patch('/cart/update', [App\Http\Controllers\CheckoutController::class, 'updateCart'])->name('store.cart.update');
    Route::delete('/cart/remove/{id}', [App\Http\Controllers\CheckoutController::class, 'removeFromCart'])->name('store.cart.remove');
  

        // SHIPPING ROUTES (RADIUS SYSTEM)
    Route::get('/shipping', [App\Http\Controllers\Client\ShippingController::class, 'index'])->name('client.shipping.index');
    Route::post('/shipping/range', [App\Http\Controllers\Client\ShippingController::class, 'store'])->name('client.shipping.store');
    Route::put('/shipping/location', [App\Http\Controllers\Client\ShippingController::class, 'updateLocation'])->name('client.shipping.updateLocation'); // Route baru untuk update lokasi
    Route::delete('/shipping/{range}', [App\Http\Controllers\Client\ShippingController::class, 'destroy'])->name('client.shipping.destroy');
    // Batasi maksimal 5 request per 1 menit per IP
    Route::middleware(['throttle:5,1'])->group(function () {
        Route::post('/checkout', [App\Http\Controllers\CheckoutController::class, 'processCheckout'])->name('store.checkout');
        
    });
        // Route Konfirmasi Pembayaran
    Route::get('/payment/{order_number}', [App\Http\Controllers\CheckoutController::class, 'payment'])->name('store.payment');
    Route::post('/payment/{order_number}', [App\Http\Controllers\CheckoutController::class, 'confirmPayment'])->name('store.payment.confirm');
    
    // === ROUTE BLOG (SUDAH BENAR) ===
    Route::get('/blog', [App\Http\Controllers\StorefrontController::class, 'blogIndex'])->name('store.blog');
    Route::get('/blog/{slug}', [App\Http\Controllers\StorefrontController::class, 'blogShow'])->name('store.blog.show');
})->name('store.');
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
        Route::resource('packages', App\Http\Controllers\Admin\PackageController::class);
        // ... di dalam grup admin ...
   
        // Manajemen Transaksi
        Route::get('/transactions', [App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');
        Route::put('/transactions/{transaction}', [App\Http\Controllers\Admin\TransactionController::class, 'update'])->name('transactions.update');
        // ... route transaksi ...
   
        // Manajemen Website
        Route::get('/websites', [App\Http\Controllers\Admin\WebsiteController::class, 'index'])->name('websites.index');
        Route::delete('/websites/{website}', [App\Http\Controllers\Admin\WebsiteController::class, 'destroy'])->name('websites.destroy');
        Route::get('/users/{id}/impersonate', [App\Http\Controllers\Admin\UserController::class, 'impersonate'])->name('users.impersonate');
        Route::delete('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    });