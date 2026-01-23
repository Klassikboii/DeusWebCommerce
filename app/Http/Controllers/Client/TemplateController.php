<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Website $website)
    {
        if ($website->user_id !== auth()->id()) abort(403);
        
        // Data Dummy Template yang tersedia
        $templates = [
            [
                'id' => 'modern',
                'name' => 'Modern Blue',
                'description' => 'Tampilan profesional dengan banner besar.',
                'image' => 'https://via.placeholder.com/300x200?text=Modern+Theme' // Nanti bisa diganti screenshot asli
            ],
            [
                'id' => 'simple',
                'name' => 'Clean Minimalist',
                'description' => 'Fokus pada produk dengan desain putih bersih.',
                'image' => 'https://via.placeholder.com/300x200?text=Simple+Theme'
            ]
        ];

        return view('client.templates.index', compact('website', 'templates'));
    }

    public function update(Request $request, Website $website)
    {
        if ($website->user_id !== auth()->id()) abort(403);

        $request->validate(['template_id' => 'required|string']);

        $website->update(['active_template' => $request->template_id]);

        return redirect()->back()->with('success', 'Template berhasil diganti!');
    }
}