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
use App\Http\Controllers\Admin\DemoRequestController;
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

// Fallback para servir arquivos do storage sem depender de symlink simbólico
Route::get('/storage/{path}', function (string $path) {
    $baseDir = realpath(storage_path('app/public'));

    abort_if($baseDir === false, 404);

    $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . str_replace(['../', '..\\'], '', $path));

    abort_if($filePath === false || !str_starts_with($filePath, $baseDir), 404);

    return response()->file($filePath);
})->where('path', '.*');

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {
    // Rota home para usuários comuns
    Route::get('/home', [HomeController::class, 'index'])->name('home');

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

// Rotas de login do admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

    Route::middleware(['auth.admin'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/password/first-access', [\App\Http\Controllers\Admin\Auth\FirstAccessController::class, 'show'])->name('first-access');
        Route::post('/password/first-access', [\App\Http\Controllers\Admin\Auth\FirstAccessController::class, 'update'])->name('first-access.update');

        Route::resource('demo-requests', \App\Http\Controllers\Admin\DemoRequestController::class)->only(['index', 'show', 'destroy']);
        Route::post('/demo-requests/{demoRequest}/create-user', [\App\Http\Controllers\Admin\DemoRequestController::class, 'createUser'])->name('demo-requests.create-user');
        Route::post('/demo-requests/{demoRequest}/update-status', [\App\Http\Controllers\Admin\DemoRequestController::class, 'updateStatus'])->name('demo-requests.update-status');
        Route::post('/demo-requests/{demoRequest}/update-pending', [\App\Http\Controllers\Admin\DemoRequestController::class, 'updatePending'])->name('demo-requests.update-pending');

        Route::resource('stores', StoreController::class);

        // Gerenciamento de Saques (Admin)
        Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'index'])->name('withdrawals.index');
        Route::get('/withdrawals/{withdrawal}', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'show'])->name('withdrawals.show');
        Route::post('/withdrawals/{withdrawal}/approve', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'reject'])->name('withdrawals.reject');
        Route::post('/withdrawals/{withdrawal}/complete', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'complete'])->name('withdrawals.complete');
        Route::get('/withdrawals-history', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'history'])->name('withdrawals.history');
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

//Rotas de solicitação de demostralção - Acesso público para criar solicitação, acesso admin para gerenciar
Route::post('/demo-requests', [DemoRequestController::class, 'createDemoRequest'])->name('demo-requests.create');

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
    Route::get('/orders/partial', [EmployeeController::class, 'kitchenOrdersPartial'])->name('orders.partial');
    Route::put('/orders/{order}/status', [EmployeeController::class, 'kitchenUpdateStatus'])->name('update-status');
});

// Rotas do Garçom
Route::middleware(['auth', 'role:waiter'])->prefix('waiter')->name('waiter.')->group(function () {
    Route::get('/dashboard', [EmployeeController::class, 'waiterDashboard'])->name('dashboard');
    Route::post('/start-attending', [EmployeeController::class, 'startAttending'])->name('start-attending');
    Route::get('/attendance-status', [EmployeeController::class, 'attendanceStatus'])->name('attendance-status');
    Route::post('/stop-attending', [EmployeeController::class, 'stopAttending'])->name('stop-attending');
    Route::get('/history', [EmployeeController::class, 'waiterHistory'])->name('history');
    Route::get('/table/{table}', [EmployeeController::class, 'waiterTableDetails'])->name('table-details');
    Route::put('/orders/{order}', [EmployeeController::class, 'waiterUpdateOrder'])->name('orders.update');
    Route::post('/table/{table}/clear', [EmployeeController::class, 'waiterClearTable'])->name('table.clear');
    Route::post('/employee/{order}/mark-paid-cash', [EmployeeController::class, 'markAsPaidCash'])->name('employee.mark-paid-cash');
});

// Downloads de APK da impressora
Route::middleware(['auth'])->group(function () {
    Route::get('/printer-apks/latest', [\App\Http\Controllers\PrinterApkController::class, 'latest'])->name('printer-apks.latest');
    Route::get('/printer-apks/versions', [\App\Http\Controllers\PrinterApkController::class, 'versions'])->name('printer-apks.versions');
    Route::get('/printer-apks/download/{folder}/{file}', [\App\Http\Controllers\PrinterApkController::class, 'download'])->name('printer-apks.download');
});

require __DIR__ . '/auth.php';
