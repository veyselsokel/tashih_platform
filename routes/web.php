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

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');

Route::get('/iletisim', [ContactController::class, 'index'])->name('contact.index');
Route::post('/iletisim', [ContactController::class, 'store'])->name('contact.store');

Route::get('/siir', function () {
    return Inertia::render('PoemPage');
})->name('poetry');

Route::get('/hakkimizda', function () {
    return Inertia::render('AboutUs', [
        'title' => 'Hakkımızda'
    ]);
});

Route::get('/hizmetlerimiz', function () {
    return Inertia::render('Services');
})->name('services');

// Yetkilendirme Gerektiren Rotalar
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard-data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

    // Blog Yönetimi
    Route::get('/blog/create', [BlogController::class, 'create'])->name('create-blog');
    Route::post('/blog', [BlogController::class, 'store'])->name('blog.store');

    // Profil Yönetimi
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Düzeltme İstekleri
    Route::get('/correction-requests', [CorrectionRequestController::class, 'index'])->name('correction-requests.index');
    Route::get('/correction-requests/{id}', [CorrectionRequestController::class, 'show'])->name('correction-requests.show');
    Route::post('/correction-requests', [CorrectionRequestController::class, 'store'])->name('correction-requests.store');
});

Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('posts.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/blog/{slug}/edit', [BlogController::class, 'edit'])->name('blog.edit');
    Route::put('/blog/{slug}', [BlogController::class, 'update'])->name('blog.update');
    Route::delete('/blog/{slug}', [BlogController::class, 'destroy'])->name('blog.destroy');
});

require __DIR__ . '/auth.php';
