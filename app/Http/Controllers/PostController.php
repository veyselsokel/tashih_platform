<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')
            ->published()
            ->latest('published_at')
            ->get();

        return response()->json($posts);
    }

    public function show(Post $post)
    {
        if (!$post->is_published && auth()->id() !== $post->user_id) {
            abort(404);
        }

        $post->load('user');
        return Inertia::render('Posts/Show', [
            'post' => $post
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_published' => 'boolean'
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_published' => $validated['is_published'] ?? false,
            'published_at' => $validated['is_published'] ? now() : null
        ]);

        return response()->json($post, 201);
    }
}
