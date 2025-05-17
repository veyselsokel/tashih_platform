<?php

namespace App\Http\Controllers;

use App\Models\BlogPost; // Assuming your model is BlogPost
use App\Models\Category; // If you use categories
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // For logging errors
use Carbon\Carbon; // For date handling

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('user')
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('BlogPage', [
            'posts' => $posts,
            'title' => 'Blog'
        ]);
    }

    public function create(Request $request)
    {
        $categories = [];
        if (class_exists(Category::class)) {
            $categories = Category::select('id', 'name')->orderBy('name')->get();
        }

        return Inertia::render('Blog/Create', [
            'title' => 'Yeni Blog Yazısı',
            'categories' => $categories,
            'user' => $request->user() ? ['id' => $request->user()->id, 'name' => $request->user()->name] : null,
            'can' => [], // Define any permissions
            'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : (object)[],
        ]);
    }

    public function store(Request $request)
    {
        // Validate the request including the 'status' from frontend
        $validatedRequestData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'formatting' => 'nullable|array',
            'formatting.font' => 'nullable|string',
            'formatting.fontSize' => 'nullable|string',
            'formatting.lineHeight' => 'nullable|string',
            'formatting.textAlign' => 'nullable|string',
            'formatting.color' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'meta_description' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|max:50',
            'status' => 'required|string|in:draft,published', // 'status' from frontend
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'nullable|exists:categories,id',
            'scheduled_at' => 'nullable|date',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_captions' => 'nullable|array',
            'gallery_captions.*' => 'nullable|string|max:255',
            'gallery_alts' => 'nullable|array',
            'gallery_alts.*' => 'nullable|string|max:255',
        ]);

        // Prepare data for database insertion.
        // CRITICAL: Ensure 'status' is NOT a key here if it's not a DB column.
        $dataForCreate = [
            'title' => $validatedRequestData['title'],
            'content' => $validatedRequestData['content'],
            'formatting' => array_merge([
                'font' => 'Arial, sans-serif',
                'fontSize' => '16px',
                'lineHeight' => '1.5',
                'textAlign' => 'left',
                'color' => '#000000'
            ], $validatedRequestData['formatting'] ?? []),
            'meta_description' => $validatedRequestData['meta_description'] ?? null,
            'tags' => $validatedRequestData['tags'] ?? [],
            'slug' => BlogPost::generateUniqueSlug($validatedRequestData['title']),
            'user_id' => auth()->id(),
            'featured_image' => null,
            'scheduled_at' => null,
            'published_at' => null,
            'is_published' => false, // DB column
            'is_draft' => true,    // DB column
        ];

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('blog/featured', 'public');
            $dataForCreate['featured_image'] = $path;
        }

        $statusInput = $validatedRequestData['status']; // This is 'draft' or 'published' from frontend
        $isActuallyPublished = ($statusInput === 'published');

        // Set database columns based on the logic derived from $statusInput
        // Ensure your BlogPost model's $fillable array includes these actual DB columns:
        // 'is_published', 'is_draft', 'published_at', 'scheduled_at'.
        // And critically, ensure 'status' is NOT in $fillable if it's not a DB column.

        if ($isActuallyPublished) {
            $dataForCreate['is_draft'] = false;
            $dataForCreate['is_published'] = true;

            if (!empty($validatedRequestData['scheduled_at'])) {
                try {
                    $scheduledTime = Carbon::parse($validatedRequestData['scheduled_at']);
                    $dataForCreate['scheduled_at'] = $scheduledTime->format('Y-m-d H:i:s');

                    if (now()->gte($scheduledTime)) {
                        $dataForCreate['published_at'] = $scheduledTime;
                    } else {
                        $dataForCreate['is_published'] = false;
                        $dataForCreate['is_draft'] = true;
                        $dataForCreate['published_at'] = null;
                    }
                } catch (\Exception $e) {
                    Log::error('Error parsing scheduled_at date: ' . $validatedRequestData['scheduled_at'] . ' - ' . $e->getMessage());
                    $dataForCreate['published_at'] = now();
                    $dataForCreate['scheduled_at'] = null;
                }
            } else {
                $dataForCreate['published_at'] = now();
                $dataForCreate['scheduled_at'] = null;
            }
        } else { // It's a draft
            $dataForCreate['is_draft'] = true;
            $dataForCreate['is_published'] = false;
            $dataForCreate['published_at'] = null;
            $dataForCreate['scheduled_at'] = null;
        }

        // Log the data being sent to create to help debug
        Log::debug('Data for BlogPost create:', $dataForCreate);

        // Create the blog post
        $blogPost = BlogPost::create($dataForCreate);

        if (!empty($validatedRequestData['category_ids']) && method_exists($blogPost, 'categories')) {
            $blogPost->categories()->sync($validatedRequestData['category_ids']);
        }

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $file) {
                try {
                    $galleryPath = $file->store('blog/gallery', 'public');
                    if (method_exists($blogPost, 'gallery')) {
                        $blogPost->gallery()->create([
                            'image_path' => $galleryPath,
                            'caption' => $validatedRequestData['gallery_captions'][$index] ?? null,
                            'alt_text' => $validatedRequestData['gallery_alts'][$index] ?? $blogPost->title . ' galeri ' . ($index + 1),
                            'order' => $index + 1,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Galeri görseli yükleme hatası (index {$index}): " . $e->getMessage());
                }
            }
        }

        return redirect()->route('dashboard')->with('success', 'Blog yazısı başarıyla oluşturuldu.');
    }

    public function show($slug)
    {
        $post = BlogPost::with(['user', 'gallery', 'categories'])
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Blog/Show', [
            'post' => $post,
            'title' => $post->title
        ]);
    }

    public function edit($slug, Request $request)
    {
        $post = BlogPost::with(['categories', 'gallery'])->where('slug', $slug)->firstOrFail();

        $categories = [];
        if (class_exists(Category::class)) {
            $categories = Category::select('id', 'name')->orderBy('name')->get();
        }

        return Inertia::render('Blog/Edit', [
            'blogPost' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'content' => $post->content,
                'meta_description' => $post->meta_description,
                'tags' => $post->tags ?? [],
                'featured_image_url' => $post->featured_image_url,
                'formatting' => $post->formatting,
                'status' => $post->is_published ? 'published' : 'draft',
                'published_at' => $post->published_at ? $post->published_at->format('Y-m-d H:i:s') : null,
                'scheduled_at' => $post->scheduled_at ? Carbon::parse($post->scheduled_at)->format('Y-m-d\TH:i:s') : null,
                'category_ids' => $post->categories->pluck('id')->toArray() ?? [],
                'gallery' => $post->gallery->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'image_url' => $item->image_url,
                        'caption' => $item->caption,
                        'alt_text' => $item->alt_text,
                        'order' => $item->order,
                    ];
                }) ?? [],
            ],
            'categories' => $categories,
            'title' => 'Yazıyı Düzenle: ' . $post->title,
            'can' => [],
            'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : (object)[],
        ]);
    }

    public function update(Request $request, $slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();

        $validatedRequestData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'formatting' => 'nullable|array',
            'meta_description' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|max:50',
            'status' => 'required|string|in:draft,published',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'nullable|exists:categories,id',
            'scheduled_at' => 'nullable|date',
            'featured_image' => 'nullable|image|max:2048',
            'remove_featured_image' => 'nullable|boolean',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_captions' => 'nullable|array',
            'gallery_alts' => 'nullable|array',
            'remove_gallery_images' => 'nullable|array',
            'remove_gallery_images.*' => 'nullable|integer|exists:blog_post_galleries,id',
        ]);

        $dataForUpdate = [
            'title' => $validatedRequestData['title'],
            'content' => $validatedRequestData['content'],
            'formatting' => array_merge(
                $post->formatting ?? ['font' => 'Arial, sans-serif', /* other defaults */],
                $validatedRequestData['formatting'] ?? []
            ),
            'meta_description' => $validatedRequestData['meta_description'] ?? null,
            'tags' => $validatedRequestData['tags'] ?? [],
        ];

        if ($post->title !== $dataForUpdate['title']) {
            $dataForUpdate['slug'] = BlogPost::generateUniqueSlug($dataForUpdate['title'], $post->id);
        }

        if ($request->boolean('remove_featured_image') && $post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
            $dataForUpdate['featured_image'] = null;
        } elseif ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $path = $request->file('featured_image')->store('blog/featured', 'public');
            $dataForUpdate['featured_image'] = $path;
        }

        $statusInput = $validatedRequestData['status'];
        $isActuallyPublished = ($statusInput === 'published');

        // Initialize with current values to avoid unintentionally nullifying them
        $dataForUpdate['is_published'] = $post->is_published;
        $dataForUpdate['is_draft'] = $post->is_draft;
        $dataForUpdate['published_at'] = $post->published_at;
        $dataForUpdate['scheduled_at'] = $post->scheduled_at;


        if ($isActuallyPublished) {
            $dataForUpdate['is_draft'] = false;
            $dataForUpdate['is_published'] = true;

            if (!empty($validatedRequestData['scheduled_at'])) {
                try {
                    $scheduledTime = Carbon::parse($validatedRequestData['scheduled_at']);
                    $dataForUpdate['scheduled_at'] = $scheduledTime->format('Y-m-d H:i:s');

                    if (now()->gte($scheduledTime)) {
                        if (!$post->is_published || $post->published_at === null) {
                             $dataForUpdate['published_at'] = $scheduledTime;
                        }
                    } else {
                        $dataForUpdate['is_published'] = false;
                        $dataForUpdate['is_draft'] = true;
                        $dataForUpdate['published_at'] = null;
                    }
                } catch (\Exception $e) {
                     Log::error('Error parsing scheduled_at date during update: ' . $validatedRequestData['scheduled_at'] . ' - ' . $e->getMessage());
                     $dataForUpdate['scheduled_at'] = $post->scheduled_at;
                     if(empty($post->scheduled_at) && $post->is_published) $dataForUpdate['published_at'] = $post->published_at ?? now();
                }
            } else {
                 if (!$post->is_published || $post->published_at === null) {
                    $dataForUpdate['published_at'] = now();
                }
                $dataForUpdate['scheduled_at'] = null;
            }
        } else { // Draft
            $dataForUpdate['is_draft'] = true;
            $dataForUpdate['is_published'] = false;
            $dataForUpdate['published_at'] = null;
            $dataForUpdate['scheduled_at'] = null;
        }

        Log::debug('Data for BlogPost update:', $dataForUpdate);
        $post->update($dataForUpdate);

        if (array_key_exists('category_ids', $validatedRequestData) && method_exists($post, 'categories')) {
            $post->categories()->sync($validatedRequestData['category_ids'] ?? []);
        }

        if ($request->input('remove_gallery_images') && method_exists($post, 'gallery')) {
            foreach ($request->input('remove_gallery_images', []) as $galleryImageId) {
                $image = $post->gallery()->find($galleryImageId);
                if ($image) {
                    $image->delete();
                }
            }
        }
        if ($request->hasFile('gallery_images') && method_exists($post, 'gallery')) {
            $order = $post->gallery()->max('order') ?? 0;
            foreach ($request->file('gallery_images') as $index => $file) {
                 try {
                    $galleryPath = $file->store('blog/gallery', 'public');
                    $post->gallery()->create([
                        'image_path' => $galleryPath,
                        'caption' => $validatedRequestData['gallery_captions'][$index] ?? null,
                        'alt_text' => $validatedRequestData['gallery_alts'][$index] ?? $post->title . ' galeri ' . ($order + $index + 1),
                        'order' => $order + $index + 1,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Galeri güncelleme - görsel yükleme hatası (index {$index}): " . $e->getMessage());
                }
            }
        }

        return redirect()->back()->with('success', 'Blog yazısı başarıyla güncellendi.');
    }

    public function saveDraft(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|exists:blog_posts,id',
                'title' => $request->input('id') ? 'nullable|string|max:255' : 'required|string|max:255',
                'content' => 'nullable|string',
                'formatting' => 'nullable|array',
                'meta_description' => 'nullable|string|max:160',
                'tags' => 'nullable|array',
            ]);

            $blogData = [
                'title' => $request->title,
                'content' => $request->content ?? '',
                'formatting' => $request->input('formatting', ['font' => 'Arial', 'fontSize' => '16px', /* other defaults */]),
                'meta_description' => $request->meta_description,
                'tags' => $request->tags ?? [],
                'is_published' => false,
                'is_draft' => true,
                'published_at' => null,
                'scheduled_at' => null,
                'user_id' => auth()->id(),
            ];

            if ($request->id) {
                $blog = BlogPost::where('user_id', auth()->id())->findOrFail($request->id);
                if ($request->filled('title') && $blog->title !== $request->title) {
                    $blogData['slug'] = BlogPost::generateUniqueSlug($request->title, $blog->id);
                } else {
                    unset($blogData['slug']); // Do not update slug if title is not provided or not changed
                }
                $blog->update($blogData);
            } else {
                 $blogData['slug'] = BlogPost::generateUniqueSlug($request->title);
                 $blog = BlogPost::create($blogData);
            }

            return back()->with([
                'success' => 'Taslak başarıyla kaydedildi.',
                'blog_post_id' => $blog->id, // For Create.vue to know the ID for subsequent auto-saves
                'slug' => $blog->slug,       // For Create.vue if it needs to switch to edit mode
            ])->withInput();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Draft save error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Taslak kaydedilemedi: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        if ($post->user_id !== auth()->id() /* && !auth()->user()->isAdmin() */) {
             return redirect()->route('dashboard')->with('error', 'Bu yazıyı silme yetkiniz yok.');
        }
        $post->delete();
        return redirect()->route('dashboard')->with('success', 'Blog yazısı başarıyla silindi.');
    }
}
