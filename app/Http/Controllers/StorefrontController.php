<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // <--- WAJIB IMPORT
use App\Models\Website;

class StorefrontController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Website dari Middleware
        $website = $request->attributes->get('website');

        // 2. SAFETY NET: Jika website NULL (berarti Admin nyasar), lempar ke Login
        if (!$website) {
            return redirect()->route('login');
        }

        $products = $website->products()->with('category')->latest()->get();

        // Logika Template
        $template = $website->active_template ?? 'modern'; 
        if (!view()->exists("templates.{$template}.home")) {
            $template = 'modern';
        }

        return view('storefront.index', compact('website', 'products'));
    }
    
    public function blogIndex(Request $request)
    {
        $website = $request->attributes->get('website');
        if (!$website) return redirect()->route('login'); // Safety Net

        $posts = $website->posts()->where('status', 'published')->latest()->paginate(10);
        return view('storefront.blog.index', compact('website', 'posts'));
    }

    public function blogShow(Request $request, $slug)
    {
        $website = $request->attributes->get('website');
        if (!$website) return redirect()->route('login'); // Safety Net
        
        $post = $website->posts()->where('slug', $slug)->where('status', 'published')->firstOrFail();
        return view('storefront.blog.show', compact('website', 'post'));
    }
}