<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category; // Eklendi
use App\Http\Requests\StoreBlogPostRequest; // Eklendi
use App\Http\Requests\UpdateBlogPostRequest; // Eklendi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

class BlogPostController extends Controller
{
    /**
     * Display a listing of the resource.
     * Admin paneli için blog yazılarını listeler.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', BlogPost::class); // Policy

        $query = BlogPost::with('user:id,name', 'categories:id,name') // Kullanıcı ve kategori bilgilerini çek
                         ->latest('updated_at'); // En son güncellenene göre sırala

        // Arama
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        // Durum filtresi (published, draft)
        if ($request->has('status') && in_array($request->status, ['published', 'draft'])) {
            $query->where('status', $request->status);
        }

        $blogPosts = $query->paginate(10)->withQueryString();

        return Inertia::render('Blog/Index', [
            'blogPosts' => $blogPosts,
            'filters' => $request->only(['search', 'status']),
            'can' => [
                'create_blog_post' => $request->user()->can('create', BlogPost::class),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Yeni blog yazısı oluşturma formunu gösterir.
     */
    public function create(Request $request)
    {
        $this->authorize('create', BlogPost::class); // Policy

        return Inertia::render('Blog/Create', [
            'categories' => Category::select('id', 'name')->orderBy('name')->get(),
            'can' => [
                'publish_blog_post' => $request->user()->can('publish', BlogPost::class),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Yeni blog yazısını veritabanına kaydeder.
     */
    public function store(StoreBlogPostRequest $request)
    {
        $validatedData = $request->validated();
        $user = $request->user();

        try {
            $blogPost = new BlogPost();
            $blogPost->user_id = $user->id;
            $blogPost->title = $validatedData['title'];
            $blogPost->slug = BlogPost::generateUniqueSlug($validatedData['title']); // Slug oluştur
            $blogPost->content = $validatedData['content'];
            $blogPost->meta_description = $validatedData['meta_description'] ?? null;
            $blogPost->tags = $validatedData['tags'] ?? [];
            $blogPost->formatting = $validatedData['formatting'] ?? [
                'font' => 'Arial',
                'fontSize' => '16px',
                'lineHeight' => '1.5',
                'textAlign' => 'left',
                'color' => '#333333'
            ];
            $blogPost->scheduled_at = isset($validatedData['scheduled_at']) ? Carbon::parse($validatedData['scheduled_at']) : null;
            $blogPost->status = $validatedData['status'] ?? 'draft';

            // Öne çıkan görsel yükleme
            if ($request->hasFile('featured_image')) {
                $path = $request->file('featured_image')->store('blog/featured', 'public');
                $blogPost->featured_image = $path;
            }

            $blogPost->save(); // Önce postu kaydet, ID alması için

            // Kategorileri eşleştir
            if (!empty($validatedData['category_ids'])) {
                $blogPost->categories()->sync($validatedData['category_ids']);
            }

            // Galeri görsellerini yükleme
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $index => $file) {
                    $galleryPath = $file->store('blog/gallery', 'public');
                    $blogPost->gallery()->create([
                        'image' => $galleryPath,
                        'caption' => $validatedData['gallery_captions'][$index] ?? null,
                        'alt_text' => $validatedData['gallery_alts'][$index] ?? $blogPost->title . ' galeri görseli ' . ($index + 1),
                        'order' => $index + 1,
                    ]);
                }
            }

            // Duruma göre yayınla veya taslakta bırak
            if ($validatedData['status'] === 'published') {
                // Policy kontrolü (eğer publish yetkisi ayrıysa)
                // $this->authorize('publish', $blogPost);
                $blogPost->publish(); // Model içindeki publish metodu
            }


            return redirect()->route('admin.blog.index')->with('success', 'Blog yazısı başarıyla oluşturuldu.');

        } catch (\Exception $e) {
            Log::error("Blog yazısı oluşturma hatası: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['featured_image', 'gallery_images']) // Hassas verileri loglama
            ]);
            return back()->withInput()->with('error', 'Blog yazısı oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Belirli bir blog yazısını gösterir (Genelde public taraf için).
     * Admin panelinde düzenleme için show yerine edit kullanılır.
     */
    public function show(BlogPost $blogPost, Request $request)
    {
        // Eğer sadece yayınlanmış ve zamanı gelmiş yazılar gösterilecekse:
        if ($blogPost->status !== 'published' || ($blogPost->published_at && $blogPost->published_at->isFuture())) {
             // Eğer admin değilse veya yazının sahibi değilse 404
            if (!$request->user() || (!$request->user()->isAdmin() && $request->user()->id !== $blogPost->user_id)) {
                 abort(404);
            }
        }

        $blogPost->load('user:id,name', 'categories:id,name,slug', 'gallery');

        return Inertia::render('Blog/Show', [ // Public view
            'blogPost' => $blogPost,
            'relatedPosts' => BlogPost::published()
                                ->where('id', '!=', $blogPost->id)
                                ->whereHas('categories', function ($query) use ($blogPost) {
                                    $query->whereIn('categories.id', $blogPost->categories->pluck('id'));
                                })
                                ->inRandomOrder()
                                ->take(3)
                                ->get(['id', 'title', 'slug', 'featured_image', 'published_at']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * Belirli bir blog yazısını düzenleme formunu gösterir.
     */
    public function edit(BlogPost $blogPost, Request $request) // Rota Model Binding
    {
        $this->authorize('update', $blogPost); // Policy

        $blogPost->load('categories:id,name', 'gallery'); // İlişkili kategorileri ve galeriyi yükle

        // Galeri için image_url ekleyelim
        $galleryWithUrls = $blogPost->gallery->map(function ($item) {
            $item->image_url = $item->imageUrl; // Accessor'ı kullan
            return $item;
        });


        return Inertia::render('Blog/Edit', [
            'blogPost' => [ // Sadece gerekli alanları ve formatlanmış veriyi gönder
                'id' => $blogPost->id,
                'title' => $blogPost->title,
                'slug' => $blogPost->slug,
                'content' => $blogPost->content,
                'meta_description' => $blogPost->meta_description,
                'tags' => $blogPost->tags,
                'featured_image_url' => $blogPost->featured_image_url, // Accessor ile URL
                'formatting' => $blogPost->formatting,
                'status' => $blogPost->status,
                'published_at' => $blogPost->published_at ? $blogPost->published_at->format('Y-m-d H:i:s') : null,
                'scheduled_at' => $blogPost->scheduled_at ? $blogPost->scheduled_at->format('Y-m-d H:i:s') : null,
                'category_ids' => $blogPost->categories->pluck('id')->toArray(), // Sadece ID'leri
                'gallery' => $galleryWithUrls, // URL'ler ile galeri
                // Yetkiler
                'can_delete_post' => $request->user()->can('delete', $blogPost),
                'can_publish_post' => $request->user()->can('publish', $blogPost),
            ],
            'categories' => Category::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Belirli bir blog yazısını günceller.
     */
    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost) // Rota Model Binding
    {
        $this->authorize('update', $blogPost); // Policy
        $validatedData = $request->validated();

        try {
            $blogPost->title = $validatedData['title'];
            // Slug sadece başlık değiştiyse ve request'te varsa güncellenir (prepareForValidation'da halledildi)
            if (isset($validatedData['slug'])) {
                $blogPost->slug = BlogPost::generateUniqueSlug($validatedData['title'], $blogPost->id);
            }
            $blogPost->content = $validatedData['content'];
            $blogPost->meta_description = $validatedData['meta_description'] ?? null;
            $blogPost->tags = $validatedData['tags'] ?? [];
            $blogPost->formatting = $validatedData['formatting'] ?? $blogPost->formatting;
            $blogPost->scheduled_at = isset($validatedData['scheduled_at']) ? Carbon::parse($validatedData['scheduled_at']) : null;
            $blogPost->status = $validatedData['status'] ?? 'draft';

            // Öne çıkan görsel güncelleme
            if ($request->hasFile('featured_image')) {
                // Eski görseli sil
                if ($blogPost->featured_image && Storage::disk('public')->exists($blogPost->featured_image)) {
                    Storage::disk('public')->delete($blogPost->featured_image);
                }
                $path = $request->file('featured_image')->store('blog/featured', 'public');
                $blogPost->featured_image = $path;
            } elseif ($request->boolean('remove_featured_image')) { // Öne çıkan görseli kaldırma isteği
                 if ($blogPost->featured_image && Storage::disk('public')->exists($blogPost->featured_image)) {
                    Storage::disk('public')->delete($blogPost->featured_image);
                }
                $blogPost->featured_image = null;
            }


            // Kategorileri güncelle
            $blogPost->categories()->sync($validatedData['category_ids'] ?? []);

            // Silinmesi istenen galeri görsellerini sil
            if (!empty($validatedData['remove_gallery_images'])) {
                foreach ($validatedData['remove_gallery_images'] as $galleryImageId) {
                    $image = $blogPost->gallery()->find($galleryImageId);
                    if ($image) {
                        $image->delete(); // Bu, BlogPostGallery modelindeki 'deleting' event'ini tetikleyerek dosyayı da siler
                    }
                }
            }

            // Yeni galeri görselleri ekleme (var olanları koruyarak)
            if ($request->hasFile('gallery_images')) {
                $order = $blogPost->gallery()->max('order') ?? 0;
                foreach ($request->file('gallery_images') as $index => $file) {
                    $galleryPath = $file->store('blog/gallery', 'public');
                    $blogPost->gallery()->create([
                        'image' => $galleryPath,
                        'caption' => $request->input("gallery_captions.{$index}") ?? null,
                        'alt_text' => $request->input("gallery_alts.{$index}") ?? $blogPost->title . ' galeri görseli ' . ($order + $index + 1),
                        'order' => $order + $index + 1,
                    ]);
                }
            }

            // Duruma göre yayınla veya taslakta bırak
            if ($validatedData['status'] === 'published') {
                // Policy kontrolü
                // $this->authorize('publish', $blogPost);
                $blogPost->publish();
            } else {
                $blogPost->unpublish(); // Veya saveDraft() - unpublish daha uygun
            }
            // $blogPost->save() publish/unpublish içinde zaten var.

            return redirect()->route('admin.blog.index')->with('success', 'Blog yazısı başarıyla güncellendi.');

        } catch (\Exception $e) {
            Log::error("Blog yazısı güncelleme hatası (ID: {$blogPost->id}): " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['featured_image', 'gallery_images'])
            ]);
            return back()->withInput()->with('error', 'Blog yazısı güncellenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Belirli bir blog yazısını siler.
     */
    public function destroy(BlogPost $blogPost) // Rota Model Binding
    {
        $this->authorize('delete', $blogPost); // Policy

        try {
            $blogPost->delete(); // Bu, BlogPost modelindeki 'deleting' event'ini tetikler
            return redirect()->route('admin.blog.index')->with('success', 'Blog yazısı başarıyla silindi.');
        } catch (\Exception $e) {
            Log::error("Blog yazısı silme hatası (ID: {$blogPost->id}): " . $e->getMessage());
            return back()->with('error', 'Blog yazısı silinirken bir hata oluştu.');
        }
    }

    

    /**
     * API endpoint to upload an image for the rich text editor (e.g., TinyMCE).
     */
    public function uploadEditorImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('file')) {
            try {
                $path = $request->file('file')->store('blog/editor_uploads', 'public');
                return response()->json(['location' => Storage::url($path)]);
            } catch (\Exception $e) {
                Log::error("Editör görsel yükleme hatası: " . $e->getMessage());
                return response()->json(['error' => 'Görsel yüklenirken bir hata oluştu.'], 500);
            }
        }
        return response()->json(['error' => 'Dosya bulunamadı.'], 400);
    }
}
