<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {
    // Rota home para usuários comuns
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Rotas para administrador
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('stores', StoreController::class);
        
        // Gerenciamento de Saques (Admin)
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'index'])->name('withdrawals.index');
            Route::get('/withdrawals/{withdrawal}', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'show'])->name('withdrawals.show');
            Route::post('/withdrawals/{withdrawal}/approve', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'approve'])->name('withdrawals.approve');
            Route::post('/withdrawals/{withdrawal}/reject', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'reject'])->name('withdrawals.reject');
            Route::post('/withdrawals/{withdrawal}/complete', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'complete'])->name('withdrawals.complete');
            Route::get('/withdrawals-history', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'history'])->name('withdrawals.history');
        });
    });

    // Rotas para lojas
    Route::middleware(['role:store'])->prefix('store')->name('store.')->group(function () {
        Route::get('/dashboard', [StoreController::class, 'dashboard'])->name('dashboard');
        Route::get('/edit', [StoreController::class, 'edit'])->name('edit');
        Route::get('/manage', [StoreController::class, 'manage'])->name('manage');
        Route::put('/update', [StoreController::class, 'updateOwnStore'])->name('update');
        
        // Gerenciamento de categorias
        Route::resource('categories', CategoryController::class);
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        
        // Gerenciamento de produtos
        Route::resource('products', ProductController::class);
        Route::post('products/reorder', [ProductController::class, 'reorder'])->name('products.reorder');
        
        // Gerenciamento de mesas
        Route::resource('tables', TableController::class);
        Route::get('tables/{table}/qrcode', [TableController::class, 'generateQrCode'])->name('tables.qrcode');
        Route::get('counter/qrcode', [TableController::class, 'generateCounterQrCode'])->name('counter.qrcode');
        Route::post('tables/{table}/clear', [TableController::class, 'clear'])->name('tables.clear');
        Route::get('/menu/preview', [MenuController::class, 'preview'])->name('menu.preview');
        
        // Tela de atendimento
        Route::get('/service', [TableController::class, 'serviceScreen'])->name('service.index');
        Route::post('/service/table/{table}/pay', [TableController::class, 'payTable'])->name('service.pay');
        Route::post('/service/table/{table}/pay-partial', [TableController::class, 'payTablePartial'])->name('service.pay-partial');
        
        // Pagamento em dinheiro (garçom)
        Route::post('/service/table/{table}/cash-payment', [PaymentController::class, 'markAsCash'])->name('service.cash-payment');
        
        // Gerenciamento de funcionários
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        
        // Gerenciamento de Dados Bancários e Saques
        Route::get('/bank-account', [\App\Http\Controllers\WithdrawalController::class, 'showBankAccount'])->name('bank-account');
        Route::post('/bank-account', [\App\Http\Controllers\WithdrawalController::class, 'storeBankAccount'])->name('bank-account.store');
        Route::get('/balance', [\App\Http\Controllers\WithdrawalController::class, 'getBalance'])->name('balance');
        Route::get('/withdrawals/create', [\App\Http\Controllers\WithdrawalController::class, 'create'])->name('withdrawals.create');
        Route::post('/withdrawals', [\App\Http\Controllers\WithdrawalController::class, 'store'])->name('withdrawals.store');
        Route::get('/withdrawals/history', [\App\Http\Controllers\WithdrawalController::class, 'history'])->name('withdrawals.history');
    });
});

// Rotas públicas para clientes
Route::get('menu/{table:qr_code}', [MenuController::class, 'show'])->name('menu.show');
Route::get('payment/{qrCode}', [PaymentController::class, 'showPaymentPage'])->name('payment.show');
Route::get('payment/{qrCode}/complete', [PaymentController::class, 'paymentComplete'])->name('payment.complete');

// Rotas de perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Histórico de pedidos do usuário
    Route::get('/orders/history', [OrderController::class, 'userHistory'])->name('orders.history');
});

// Rotas de Pedidos - permitindo acesso sem autenticação
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

// Rotas de pedidos protegidas por autenticação (apenas para a loja)
Route::middleware(['auth', 'role:store'])->group(function () {
    Route::put('/store/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('store.orders.update-status');
    Route::post('/store/orders/{order}/mark-paid-cash', [OrderController::class, 'markAsPaidCash'])->name('store.orders.mark-paid-cash');
    Route::delete('/store/orders/{order}', [OrderController::class, 'cancel'])->name('store.orders.cancel');
    Route::get('/store/orders/production', [OrderController::class, 'production'])->name('store.orders.production');
    Route::get('/store/orders/history', [OrderController::class, 'history'])->name('store.orders.history');
});

// ========== ROTAS DOS FUNCIONÁRIOS ==========

// Rotas da Cozinha
Route::middleware(['auth', 'role:kitchen'])->prefix('kitchen')->name('kitchen.')->group(function () {
    Route::get('/dashboard', [EmployeeController::class, 'kitchenDashboard'])->name('dashboard');
    Route::put('/orders/{order}/status', [EmployeeController::class, 'kitchenUpdateStatus'])->name('update-status');
});

// Rotas do Garçom
Route::middleware(['auth', 'role:waiter'])->prefix('waiter')->name('waiter.')->group(function () {
    Route::get('/dashboard', [EmployeeController::class, 'waiterDashboard'])->name('dashboard');
    Route::get('/history', [EmployeeController::class, 'waiterHistory'])->name('history');
    Route::get('/table/{table}', [EmployeeController::class, 'waiterTableDetails'])->name('table-details');
    Route::post('/table/{table}/clear', [EmployeeController::class, 'waiterClearTable'])->name('table.clear');
});

require __DIR__.'/auth.php';
