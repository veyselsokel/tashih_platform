<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $blogPosts = BlogPost::with(['user', 'gallery'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return Inertia::render('Dashboard', [
            'blogPosts' => $blogPosts,
            'stats' => [
                'totalPosts' => $blogPosts->count(),
                'publishedPosts' => $blogPosts->where('is_published', true)->count(),
                'draftPosts' => $blogPosts->where('is_draft', true)->count(),
            ],
        ]);
    }

    public function getDashboardData()
    {
        $blogPosts = BlogPost::with(['user', 'gallery'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'posts' => $blogPosts,
            'stats' => [
                'totalPosts' => $blogPosts->count(),
                'publishedPosts' => $blogPosts->where('is_published', true)->count(),
                'draftPosts' => $blogPosts->where('is_draft', true)->count(),
            ]
        ]);
    }
}