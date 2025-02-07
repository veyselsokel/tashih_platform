<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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

    public function create()
    {
        return Inertia::render('Blog/Create', [
            'title' => 'Yeni Blog Yazısı'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
            'is_published' => 'boolean',
            'gallery' => 'nullable|array'
        ]);
    
        // Varsayılan format değerlerini ayarla
        $validated['formatting'] = array_merge([
            'font' => 'Arial, sans-serif',
            'fontSize' => '16px',
            'lineHeight' => '1.5',
            'textAlign' => 'left',
            'color' => '#000000'
        ], $validated['formatting'] ?? []);

        // Slug oluştur
        $validated['slug'] = Str::slug($validated['title']);
        $count = 1;
        while (BlogPost::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . $count;
            $count++;
        }

        // Featured image işle
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('blog', 'public');
            $validated['featured_image'] = $path;
        }

        $validated['user_id'] = auth()->id();
        if ($validated['is_published']) {
            $validated['published_at'] = now();
        }

        $validated['user_id'] = auth()->id();
    
        if ($validated['is_published']) {
            $validated['published_at'] = now();
            $validated['is_draft'] = false;
        } else {
            $validated['is_draft'] = true;
            $validated['published_at'] = null;
        }

        // Blog yazısını oluştur
        $blog = BlogPost::create($validated);

        // Galeri görsellerini işle
        if ($request->gallery && is_array($request->gallery)) {
            foreach ($request->gallery as $index => $galleryItem) {
                if (isset($galleryItem['file']) && is_file($galleryItem['file'])) {
                    // Doğrudan public dizinine kaydet
                    $path = $galleryItem['file']->store('blog/gallery', 'public');
                    
                    $blog->gallery()->create([
                        'image' => $path, // path'i olduğu gibi kaydet
                        'caption' => $galleryItem['caption'] ?? '',
                        'alt_text' => $galleryItem['altText'] ?? '',
                        'order' => $index
                    ]);
                }
            }
        }

        return redirect()->route('dashboard')->with('success', 'Blog yazısı başarıyla oluşturuldu.');
    }

    public function show($slug)
    {
        $post = BlogPost::with(['user', 'gallery'])
            ->where('slug', $slug)
            ->firstOrFail();
    
        return Inertia::render('Blog/Show', [
            'post' => $post,
            'title' => $post->title
        ]);
    }

    public function edit($slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();

        return Inertia::render('Blog/Edit', [
            'post' => $post,
            'title' => 'Yazıyı Düzenle'
        ]);
    }

    public function update(Request $request, $slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
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
            'is_published' => 'boolean'
        ]);
    
        $validated['formatting'] = array_merge(
            $post->formatting ?? [],
            $validated['formatting'] ?? []
        );

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('blog', 'public');
            $validated['featured_image'] = $path;
        }

        if ($validated['is_published'] && !$post->published_at) {
            $validated['published_at'] = now();
        }

        if ($validated['is_published']) {
            $validated['published_at'] = now();
            $validated['is_draft'] = false;
        } else {
            $validated['is_draft'] = true;
            $validated['published_at'] = null;
        }

        $post->update($validated);

        return redirect()->back()->with('success', 'Blog yazısı başarıyla güncellendi.');
    }

    public function saveDraft(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'nullable|string',
                'formatting' => 'nullable|array',
                'formatting.font' => 'nullable|string',
                'formatting.fontSize' => 'nullable|string',
                'formatting.lineHeight' => 'nullable|string',
                'formatting.textAlign' => 'nullable|string',
                'formatting.color' => 'nullable|string',
                'meta_description' => 'nullable|string|max:160',
                'tags' => 'nullable|array',
                'featured_image' => 'nullable|image|max:2048',
                'gallery' => 'nullable|array'
            ]);

            $validated['formatting'] = array_merge([
                'font' => 'Arial, sans-serif',
                'fontSize' => '16px',
                'lineHeight' => '1.5',
                'textAlign' => 'left',
                'color' => '#000000'
            ], $validated['formatting'] ?? []);
    
            // Slug oluşturma
            $slug = Str::slug($request->title);
            $count = 1;
            while (BlogPost::where('slug', $slug)->where('user_id', '!=', auth()->id())->exists()) {
                $slug = Str::slug($request->title) . '-' . $count;
                $count++;
            }
    
            $blog = BlogPost::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'is_draft' => true,
                    'is_published' => false
                ],
                [
                    'title' => $request->title,
                    'slug' => $slug,
                    'content' => $request->content ?? '',
                    'formatting' => $request->formatting,
                    'meta_description' => $request->meta_description,
                    'tags' => $request->tags ?? [],
                    'is_published' => false,
                    'is_draft' => true,
                    'published_at' => null
                ]
            );
    
            if ($request->hasFile('featured_image')) {
                $path = $request->file('featured_image')->store('blog', 'public');
                $blog->update(['featured_image' => $path]);
            }

            if ($request->gallery) {
                foreach ($request->gallery as $index => $galleryItem) {
                    if (isset($galleryItem['file'])) {
                        $path = $galleryItem['file']->store('blog/gallery', 'public');
                        $blog->gallery()->create([
                            'image' => $path,
                            'caption' => $galleryItem['caption'] ?? '',
                            'alt_text' => $galleryItem['altText'] ?? '',
                            'order' => $index
                        ]);
                    }
                }
            }
    
            return back()->with([
                'success' => 'Taslak başarıyla kaydedildi.',
                'blog' => $blog->fresh()
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Draft save error: ' . $e->getMessage());
            return back()->with('error', 'Taslak kaydedilemedi: ' . $e->getMessage());
        }
    }

    public function destroy($slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        $post->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Blog yazısı başarıyla silindi.');
    }
}
