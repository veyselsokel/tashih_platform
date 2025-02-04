<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Genel Sayfalar
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// İletişim Rotaları
Route::get('/iletisim', [ContactController::class, 'index'])->name('contact.index');
Route::post('/iletisim', [ContactController::class, 'store'])->name('contact.store');

// Statik Sayfalar
Route::get('/hakkimizda', function () {
    return Inertia::render('AboutUs', [
        'title' => 'Hakkımızda'
    ]);
});

Route::get('/hizmetlerimiz', function () {
    return Inertia::render('Services');
})->name('services');

// Blog Rotaları
Route::prefix('blog')->name('blog.')->group(function () {
    // Public rotalar
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');

    // Auth gerektiren rotalar
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/create/new', [BlogController::class, 'create'])->name('create');
        Route::post('/', [BlogController::class, 'store'])->name('store');
        Route::post('/draft', [BlogController::class, 'saveDraft'])->name('draft'); // Yeni eklenen
        Route::get('/{slug}/edit', [BlogController::class, 'edit'])->name('edit');
        Route::put('/{slug}', [BlogController::class, 'update'])->name('update');
        Route::delete('/{slug}', [BlogController::class, 'destroy'])->name('destroy');
    });
});

// Yetkilendirme Gerektiren Rotalar
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard-data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

    // Profil Yönetimi
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Düzeltme İstekleri
    Route::get('/correction-requests', [CorrectionRequestController::class, 'index'])->name('correction-requests.index');
    Route::get('/correction-requests/{id}', [CorrectionRequestController::class, 'show'])->name('correction-requests.show');
    Route::post('/correction-requests', [CorrectionRequestController::class, 'store'])->name('correction-requests.store');
});

require __DIR__ . '/auth.php';