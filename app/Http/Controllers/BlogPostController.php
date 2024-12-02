<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('user')
            ->where('status', 'draft') // or whatever status you want to show
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($posts);
    }

    public function show($id)
    {
        $post = BlogPost::with('user')->findOrFail($id);
        return response()->json($post);
    }
}