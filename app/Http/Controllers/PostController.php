<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    /**
     * Display a listing of blog posts
     */
    public function index()
    {
        $posts = BlogPost::with('user')
            ->latest()
            ->paginate(10);

        return Inertia::render('BlogPage', [
            'posts' => $posts
        ]);
    }

    /**
     * Show form for creating a new blog post
     */
    public function create()
    {
        return Inertia::render('Blog/Create');
    }

    /**
     * Store a newly created blog post
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published',
        ]);
    
        $post = $request->user()->blogPosts()->create($validated);
    
        if ($request->wantsJson()) {
            return response()->json($post, 201);
        }
    
        return redirect()->route('posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified blog post
     */
    public function show(BlogPost $post)
    {
        $post->load('user');

        return Inertia::render('Blog/Show', [
            'post' => $post
        ]);
    }

    /**
     * Show form for editing a blog post
     */
    public function edit(BlogPost $post)
    {
        return Inertia::render('Blog/Edit', [
            'post' => $post
        ]);
    }

    /**
     * Update the specified blog post
     */
    public function update(Request $request, BlogPost $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $path = $request->file('featured_image')->store('blog-images', 'public');
            $validated['featured_image'] = $path;
        }

        // Update slug only if title changed
        if ($post->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle publication status change
        if ($validated['status'] === 'published' && $post->status !== 'published') {
            $validated['published_at'] = now();
        } elseif ($validated['status'] === 'draft') {
            $validated['published_at'] = null;
        }

        $post->update($validated);

        return redirect()->route('posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified blog post
     */
    public function destroy(BlogPost $post)
    {
        $this->authorize('delete', $post);

        // Delete featured image if exists
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Get published blog posts for public viewing
     */
    public function published()
    {
        $posts = BlogPost::with('user')
            ->published()
            ->latest('published_at')
            ->paginate(12);

        return Inertia::render('Blog/Index', [
            'posts' => $posts
        ]);
    }

    /**
     * Toggle post publication status
     */
    public function togglePublish(BlogPost $post)
    {
        $this->authorize('update', $post);

        if ($post->status === 'published') {
            $post->unpublish();
            $message = 'Post unpublished successfully.';
        } else {
            $post->publish();
            $message = 'Post published successfully.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * API endpoint for fetching posts
     */
    public function apiIndex(Request $request)
    {
        $query = BlogPost::with('user')->latest();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(10);

        return response()->json($posts);
    }
}