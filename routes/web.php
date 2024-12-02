<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\ContactController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public routes
Route::get('/blog', [PostController::class, 'published'])->name('blog.published');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::resource('posts', PostController::class);
    Route::post('/posts/{post}/toggle-publish', [PostController::class, 'togglePublish'])
        ->name('posts.toggle-publish');
});

use App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard-data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');
    Route::resource('correction-requests', CorrectionRequestController::class);
    Route::resource('posts', PostController::class);
});

Route::get('/blog', function () {
    return Inertia::render('BlogPage');
})->name('blog');

// Post routes
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

Route::middleware('auth')->group(function () {
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});

// Correction request routes
Route::post('/correction-requests', [CorrectionRequestController::class, 'store'])->name('correction-requests.store');

Route::middleware('auth')->group(function () {
    Route::get('/correction-requests', [CorrectionRequestController::class, 'index'])->name('correction-requests.index');
    Route::get('/correction-requests/{id}', [CorrectionRequestController::class, 'show'])->name('correction-requests.show');
});

// Contact routes
Route::get('/iletisim', [ContactController::class, 'index'])->name('contact.index');
Route::post('/iletisim', [ContactController::class, 'store'])->name('contact.store');

// Additional pages
Route::get('/siir', function () {
    return Inertia::render('PoemPage');
})->name('poetry');

Route::get('/hakkimizda', function () {
    return Inertia::render('AboutUs');
})->name('about-us');

Route::get('/hizmetlerimiz', function () {
    return Inertia::render('Services');
})->name('services');

require __DIR__ . '/auth.php';