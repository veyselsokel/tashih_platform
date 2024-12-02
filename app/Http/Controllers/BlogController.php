<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $blogs = Blog::latest()->paginate(10);
        return response()->json($blogs);
    }

    public function create()
    {
        return response()->json(['message' => 'Use the store method to create a new blog post']);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $blog = Auth::user()->blogs()->create($validatedData);

        return response()->json($blog, 201);
    }

    public function show(string $id)
    {
        $blog = Blog::findOrFail($id);
        return response()->json($blog);
    }

    public function edit(string $id)
    {
        return response()->json(['message' => 'Use the update method to edit a blog post']);
    }

    public function update(Request $request, string $id)
    {
        $blog = Blog::findOrFail($id);

        $this->authorize('update', $blog);

        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $blog->update($validatedData);

        return response()->json($blog);
    }

    public function destroy(string $id)
    {
        $blog = Blog::findOrFail($id);

        $this->authorize('delete', $blog);

        $blog->delete();

        return response()->json(null, 204);
    }
}