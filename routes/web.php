
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Notifications\NewOrderNotification;

use App\Http\Controllers\PromotionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukTerlarisController;
use App\Http\Controllers\LaporanKategoriController;
use App\Http\Controllers\ProdukTerendahController;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerAddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ShippingController;

/*
|--------------------------------------------------------------------------
| HOMEPAGE
|--------------------------------------------------------------------------
*/

Route::get('/', 'App\Http\Controllers\HomeController@product')
    ->name('product');

Route::get('/home', 'App\Http\Controllers\HomeController@product')
    ->name('product');

Route::get('/syaratDanKetentuan', 'App\Http\Controllers\HomeController@termconditions')
    ->name('termconditions');

Route::get('/contact', 'App\Http\Controllers\HomeController@contact')
    ->name('contact');


/*
|--------------------------------------------------------------------------
| CUSTOMER AUTHENTICATION & ACCOUNT
|--------------------------------------------------------------------------
*/

Route::prefix('customer')
    ->name('customer.')
    ->group(function () {

        // Guest Customer
        Route::middleware('guest:customer')->group(function () {

            Route::get('/login', [CustomerAuthController::class, 'login'])
                ->name('login');

            Route::post('/login', [CustomerAuthController::class, 'authenticate'])
                ->name('authenticate');

            Route::get('/register', [CustomerAuthController::class, 'register'])
                ->name('register');

            Route::post('/register', [CustomerAuthController::class, 'store'])
                ->name('store');
        });


        // Authenticated Customer
        Route::middleware('auth:customer')->group(function () {

            Route::get('/profile', [CustomerController::class, 'profile'])
                ->name('profile');

            Route::patch('/profile', [CustomerController::class, 'updateProfile'])
                ->name('profile.update');

            Route::get('/orders', [CustomerController::class, 'orders'])
                ->name('orders');
            
            Route::get('/orders/{id}', [CustomerController::class, 'orderDetail'])
                ->name('orders.detail');

            Route::post('/logout', [CustomerAuthController::class, 'logout'])
                ->name('logout');

            Route::get('/orders/{id}/payment', [CustomerController::class, 'payment'])
                ->name('orders.payment');

            Route::post('/orders/{id}/payment-proof', [CustomerController::class, 'uploadPaymentProof'])
                ->name('orders.payment-proof');
            

            // Customer Addresses
            Route::prefix('addresses')
                ->name('addresses.')
                ->controller(CustomerAddressController::class)
                ->group(function () {

                    Route::get('/', 'index')->name('index');

                    Route::post('/', 'store')->name('store');

                    Route::get('/{id}/edit', 'edit')->name('edit');

                    Route::put('/{id}', 'update')->name('update');

                    Route::patch('/{id}/default', 'setDefault')
                        ->name('default');

                    Route::delete('/{id}', 'destroy')->name('destroy');
                });

            // Shipping
            // Shipping
            Route::prefix('shipping')
            ->name('shipping.')
            ->controller(ShippingController::class)
            ->group(function () {

                Route::get('/destination', 'searchDestination')
                    ->name('destination');

                Route::post('/calculate', 'calculate')
                    ->name('calculate');

                // RajaOngkir Location
                Route::get('/provinces', 'provinces')
                    ->name('provinces');

                Route::get('/cities/{provinceId}', 'cities')
                    ->name('cities');

                Route::get('/districts/{cityId}', 'districts')
                    ->name('districts');

                Route::get('/subdistricts/{districtId}', 'subdistricts')
                    ->name('subdistricts');
            });
        });
    });


/*
|--------------------------------------------------------------------------
| SHOPPING CART & CHECKOUT
|--------------------------------------------------------------------------
*/

Route::post('/cart/add', 'App\Http\Controllers\CartController@addToCart')
    ->name('cart.add');

Route::get('/cart', 'App\Http\Controllers\CartController@viewCart')
    ->name('cart.view');

Route::delete('/cart/{id}', 'App\Http\Controllers\CartController@remove')
    ->name('cart.remove');

Route::post('/update-cart/{id}', 'App\Http\Controllers\CartController@update')
    ->name('cart.update');

Route::post('/update-cart-note/{id}', 'App\Http\Controllers\CartController@updateNote')
    ->name('cart.update-note');

Route::post('/checkout', [CartController::class, 'checkout'])
    ->middleware('auth:customer')
    ->name('checkout');

Route::get('/success/{id}', 'App\Http\Controllers\CartController@success')
    ->name('success');

Route::post('/upload-pembayaran', 'App\Http\Controllers\CartController@upload')
    ->name('upload');

Route::get('/payment/status/{invoice}', 'App\Http\Controllers\CartController@status')
    ->name('status');


/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::get('/login', 'App\Http\Controllers\AdminController@Login')
    ->name('Login');

Route::post('/postlogin', 'App\Http\Controllers\AdminController@postlogin');

Route::get('/logout', 'App\Http\Controllers\AdminController@logout')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD & REPORTS
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

// Product Reports
Route::get('/laporan/produk/terlaris', [ProdukTerlarisController::class, 'index'])
    ->name('laporan.produk.terlaris');

Route::get('/laporan/produk/per-kategori', [LaporanKategoriController::class, 'index'])
    ->name('laporan.produk.per-kategori');

Route::get('/laporan/produk/terendah', [ProdukTerendahController::class, 'index'])
    ->name('laporan.produk.terendah');

// Sales Reports
Route::prefix('laporan/penjualan')
    ->name('laporan.penjualan.')
    ->controller(LaporanPenjualanController::class)
    ->group(function () {

        Route::get('/periode', 'periode')->name('periode');

        Route::get('/perhari', 'perHari')->name('perhari');

        Route::get('/jam', 'perJam')->name('jam');
    });


/*
|--------------------------------------------------------------------------
| ADMIN ORDER MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::post('/CloseOrder', 'App\Http\Controllers\AdminController@CloseOrder');
// ADMIN ORDER MANAGEMENT
Route::get('/dashboard/orders', [
    App\Http\Controllers\AdminController::class,
    'orderList'
])->name('admin.orders');

Route::get('/dashboard/orders/{id}', [
    App\Http\Controllers\AdminController::class,
    'orderDetail'
])->name('admin.orders.detail');

Route::post('/dashboard/orders/{id}/payment/approve', [
    App\Http\Controllers\AdminController::class,
    'approvePayment'
])->name('admin.orders.payment.approve');

Route::post('/dashboard/orders/{id}/payment/reject', [
    App\Http\Controllers\AdminController::class,
    'rejectPayment'
])->name('admin.orders.payment.reject');

Route::post('/dashboard/orders/{id}/status', [
    App\Http\Controllers\AdminController::class,
    'updateOrderStatus'
])->name('admin.orders.status');

Route::post('/dashboard/orders/{id}/shipping', [
    App\Http\Controllers\AdminController::class,
    'updateShipping'
])->name('admin.orders.shipping');


/*
|--------------------------------------------------------------------------
| MERCHANT MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::get('/dashboard/masterMerchant', 'App\Http\Controllers\AdminController@MasterMerchant')
    ->name('MasterMerchant');

Route::post('/postmerchant', 'App\Http\Controllers\AdminController@postmerchant');


/*
|--------------------------------------------------------------------------
| PRODUCT MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::get('/dashboard/masterProducts', 'App\Http\Controllers\ProductController@MasterProducts')
    ->name('MasterProducts');

Route::post('/addProducts', 'App\Http\Controllers\ProductController@AddProducts');

Route::post('/editProducts', 'App\Http\Controllers\ProductController@EditProducts');

Route::post('/activedProducts', 'App\Http\Controllers\ProductController@ActivedProducts');


/*
|--------------------------------------------------------------------------
| CATEGORY MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::get('/dashboard/masterCategories', 'App\Http\Controllers\ProductController@MasterCategories')
    ->name('MasterCategories');

Route::post('/addCategories', 'App\Http\Controllers\ProductController@AddCategories');

Route::post('/editCategories', 'App\Http\Controllers\ProductController@EditCategories');

Route::post('/activedCategories', 'App\Http\Controllers\ProductController@ActivedCategories');


/*
|--------------------------------------------------------------------------
| PROMOTION MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::get('/dashboard/masterPromotions', 'App\Http\Controllers\PromotionController@MasterPromotions')
    ->name('MasterPromotions');

Route::post('/addPromotions', 'App\Http\Controllers\PromotionController@AddPromotions');

Route::post('/editPromotions', 'App\Http\Controllers\PromotionController@EditPromotions');

Route::post('/activedPromotions', 'App\Http\Controllers\PromotionController@ActivedPromotions');

Route::get('/promotion/{id}', [PromotionController::class, 'show']);


/*
|--------------------------------------------------------------------------
| TRANSACTION MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::get('/dashboard/transaction', 'App\Http\Controllers\AdminController@transaction')
    ->name('transaction');

Route::get('/dashboard/dayTransaction', 'App\Http\Controllers\AdminController@dayTransaction')
    ->name('dayTransaction');

Route::get('/dashboard/settingorder', 'App\Http\Controllers\AdminController@settingorder')
    ->name('settingorder');

Route::get('/reportTransaction', 'App\Http\Controllers\AdminController@ReportTransaction')
    ->name('ReportTransaction');

Route::post('/updatestatus', 'App\Http\Controllers\AdminController@UpdateStatus')
    ->name('updatestatus');


/*
|--------------------------------------------------------------------------
| PUSH NOTIFICATIONS
|--------------------------------------------------------------------------
*/

Route::post('/subscribe', 'App\Http\Controllers\AdminController@subscribe')
    ->name('subscribe');

Route::get('/test-notification', function () {

    $admin = User::where('role', 'kasir')->first();

    if (!$admin) {
        return 'Admin tidak ditemukan';
    }

    Log::info('Admin ditemukan', [
        'id' => $admin->id,
    ]);

    Log::info('Subscription', [
        'count' => $admin->pushSubscriptions()->count(),
    ]);

    $order = (object) [
        'id_transaction' => 999,
        'nama' => 'Wisnu',
    ];

    try {

        $admin->notify(new NewOrderNotification($order));

        Log::info('Notifikasi berhasil dikirim');

        return 'Notifikasi terkirim';

    } catch (\Exception $e) {

        Log::error('Gagal mengirim notifikasi', [
            'message' => $e->getMessage(),
        ]);

        return $e->getMessage();
    }

});


