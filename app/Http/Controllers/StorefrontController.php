<?php

namespace App\Http\Controllers;

use App\Models\Website; 
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function index(Request $request)
    {
        $website = $request->attributes->get('website');
        $products = $website->products()->with('category')->latest()->get();

        // LOGIKA BARU: Ambil nama template dari database
        // Jika active_template = 'simple', maka view = 'templates.simple.home'
        $template = $website->active_template ?? 'modern'; 
        
        // Cek apakah view-nya ada, untuk mencegah error
        if (!view()->exists("templates.{$template}.home")) {
            $template = 'modern'; // Fallback ke modern jika error
        }

        // return view("templates.{$template}.home", compact('website', 'products'));
        return view('storefront.index', compact('website', 'products'));
    }

    // 2. BLOG INDEX
    public function blogIndex($request) // <-- Tangkap $subdomain
    {
        $website = $request->attributes->get('website');
        
        // Pastikan ada data post yang statusnya 'published'
        $posts = $website->posts()->where('status', 'published')->latest()->paginate(10);

        // Path View sesuai yang sudah kita pindahkan tadi
        return view('storefront.blog.index', compact('website', 'posts'));
    }

    // 3. BLOG SHOW
    public function blogShow($request, $slug) // <-- Tangkap $subdomain dulu, baru $slug
    {
        $website = $request->attributes->get('website');
        
        $post = $website->posts()
                        ->where('slug', $slug)
                        ->where('status', 'published')
                        ->firstOrFail(); // <-- Kalau post gak ketemu, dia otomatis 404

        return view('storefront.blog.show', compact('website', 'post'));
    }
}