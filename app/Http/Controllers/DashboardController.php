<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $correctionRequests = CorrectionRequest::with('user')
            ->latest()
            ->take(5)
            ->get();

        $blogPosts = BlogPost::with('user')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'correctionRequests' => $correctionRequests,
            'blogPosts' => $blogPosts,
            'stats' => [
                'totalRequests' => CorrectionRequest::count(),
                'pendingRequests' => CorrectionRequest::pending()->count(),
                'completedRequests' => CorrectionRequest::completed()->count(),
                'publishedPosts' => BlogPost::published()->count(),
            ],
        ]);
    }

    public function getDashboardData()
    {
        $correctionRequests = CorrectionRequest::with('user')
            ->latest()
            ->get();

        $blogPosts = BlogPost::with('user')
            ->latest()
            ->get();

        return response()->json([
            'correctionRequests' => $correctionRequests,
            'blogPosts' => $blogPosts,
        ]);
    }
}