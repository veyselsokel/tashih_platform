<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FiyatTakipApiController;
use Inertia\Inertia;
use App\Http\Controllers\CrosswordController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\FiyatTakipController;
use App\Http\Controllers\PageController; // Statik sayfalar için (örn: anasayfa, hakkımızda)


// Genel Sayfalar
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/blog', [BlogPostController::class, 'index'])->name('blog.index.public'); // Public blog listeleme
Route::get('/blog/{blogPost:slug}', [BlogPostController::class, 'show'])->name('blog.show.public'); // Tekil blog yazısı gösterme

Route::get('/fiyat-takip', 'App\Http\Controllers\FiyatTakipController@index')->name('fiyat-takip');

Route::prefix('fiyat-takip')->group(function () {
    Route::post('/start', [FiyatTakipApiController::class, 'start']);
    Route::post('/stop', [FiyatTakipApiController::class, 'stop']);
    Route::get('/status', [FiyatTakipApiController::class, 'getStatus']);
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

Route::get('/bulmacalar', function () {
    return Inertia::render('CrosswordPage', [
        'title' => 'Bulmacalar'
    ]);
})->name('bulmacalar');


Route::get('/hizmetlerimiz', function () {
    return Inertia::render('Services');
})->name('services');

Route::get('/vukuat', function () {
    return Inertia::render('Vukuat');
})->name('vukuat');

// Blog Rotaları
Route::prefix('blog')->name('blog.')->group(function () {
    // Public rotalar
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');

    // Auth gerektiren rotalar
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/create/new', [BlogController::class, 'create'])->name('create');
        Route::post('/', [BlogController::class, 'store'])->name('store');
        Route::post('/draft', [BlogController::class, 'saveDraft'])->name('draft');
        Route::get('/{slug}/edit', [BlogController::class, 'edit'])->name('edit');
        Route::post('/{slug}', [BlogController::class, 'update'])->name('update');
        Route::delete('/{slug}', [BlogController::class, 'destroy'])->name('destroy');
    });
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Blog Yönetimi
    Route::resource('blog', BlogPostController::class)->except(['show']); // Admin için show yerine edit kullanılır genelde
    // BlogPostController içinde show metodu public taraf için ayarlandı, admin için gerekirse ayrı bir show metodu veya direkt edit kullanılabilir.
    // Route::get('blog/{blogPost}', [BlogPostController::class, 'show'])->name('blog.show'); // Admin için show (isteğe bağlı)

    // Kategori Yönetimi (CategoryController oluşturulursa)
    // Route::resource('categories', CategoryController::class)->except(['show']);

    // Editör için görsel yükleme endpoint'i
    Route::post('blog/upload-editor-image', [BlogPostController::class, 'uploadEditorImage'])->name('blog.uploadEditorImage');

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
