<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

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

    // BlogController.php'de store metodunu güncelleyin:

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'formatting' => 'nullable|array',
            'featured_image' => 'nullable|image|max:2048',
            'meta_description' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'is_published' => 'boolean'
        ]);

        // Slug oluşturma
        $validated['slug'] = Str::slug($validated['title']);

        // Eğer aynı slug varsa sonuna numara ekleyelim
        $count = 1;
        while (BlogPost::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . $count;
            $count++;
        }

        if ($request->hasFile('featured_image')) {
            // Yeni yazı oluşturulduğu için eski resim kontrolüne gerek yok
            $path = $request->file('featured_image')->store('blog', 'public');
            $validated['featured_image'] = $path;
        }

        $validated['user_id'] = auth()->id();

        if ($validated['is_published']) {
            $validated['published_at'] = now();
        }

        BlogPost::create($validated);

        return redirect()->route('dashboard')->with('success', 'Blog yazısı başarıyla oluşturuldu.');
    }

    public function show($slug)
    {
        $post = BlogPost::with('user')
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
            'featured_image' => 'nullable|image|max:2048',
            'meta_description' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'is_published' => 'boolean'
        ]);

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('blog', 'public');
            $validated['featured_image'] = $path;
        }

        if ($validated['is_published'] && !$post->published_at) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        return redirect()->back()->with('success', 'Blog yazısı başarıyla güncellendi.');
    }

    public function destroy($slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        $post->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Blog yazısı başarıyla silindi.');
    }
}
