<?php

namespace App\Http\Controllers;

use App\Models\Post;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalPosts' => Post::count(),
            'publishedPosts' => Post::published()->count(),
            'draftPosts' => Post::where('status', 'draft')->count(),
            'recentPosts' => Post::with('category')->latest()->limit(6)->get(),
        ]);
    }
}
