<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function index(Website $website)
    {
        if ($website->user_id !== auth()->id()) abort(403);
        return view('client.seo.index', compact('website'));
    }

    public function update(Request $request, Website $website)
    {
        if ($website->user_id !== auth()->id()) abort(403);

        $request->validate([
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160', // Standar Google 160 char
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $website->update([
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ]);

        return redirect()->back()->with('success', 'Konfigurasi SEO berhasil disimpan!');
    }
}