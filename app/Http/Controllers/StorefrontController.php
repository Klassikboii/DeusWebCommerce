<?php

namespace App\Http\Controllers;

use App\Models\Website; 
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function index($subdomain)
    {
        $website = Website::where('subdomain', $subdomain)->firstOrFail();
        $products = $website->products()->with('category')->latest()->get();

        // LOGIKA BARU: Ambil nama template dari database
        // Jika active_template = 'simple', maka view = 'templates.simple.home'
        $template = $website->active_template ?? 'modern'; 
        
        // Cek apakah view-nya ada, untuk mencegah error
        if (!view()->exists("templates.{$template}.home")) {
            $template = 'modern'; // Fallback ke modern jika error
        }

        return view("templates.{$template}.home", compact('website', 'products'));
    }

        public function blogIndex($subdomain) {
        $website = Website::where('subdomain', $subdomain)->firstOrFail();
        $posts = $website->posts()->where('status', 'published')->latest()->get();
        return view('templates.modern.blog.index', compact('website', 'posts'));
    }

    public function blogShow($subdomain, $slug) {
        $website = Website::where('subdomain', $subdomain)->firstOrFail();
        // Cari post berdasarkan slug
        $post = $website->posts()->where('slug', $slug)->firstOrFail();
        return view('templates.modern.blog.show', compact('website', 'post'));
        //slug update
}
}