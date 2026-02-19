<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request, Website $website)
    {
        $this->authorize('viewAny', $website);
        // $posts = $website->posts()->latest()->paginate(10);
        // return view('client.posts.index', compact('website', 'posts'));
        $query = $website->posts();

        // Logika Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Pagination
        $posts = $query->latest()->paginate(10)->withQueryString();

        return view('client.posts.index', compact('website', 'posts'));
    }

    public function create(Website $website)
    {
        $this->authorize('create', $website);
        return view('client.posts.create', compact('website'));
    }

    public function store(Request $request, Website $website)
    {
        $this->authorize('create', $website);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts/' . $website->id, 'public');
        }

        $website->posts()->create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(4),
            'content' => $request->content,
            'image' => $imagePath,
            'status' => 'published',
        ]);

        return redirect()->route('client.posts.index', $website->id)->with('success', 'Artikel berhasil diterbitkan');
    }

    public function destroy(Website $website, Post $post)
    {
        if ($post->website_id !== $website->id) abort(403);
        
        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }
        
        $post->delete();
        return redirect()->back()->with('success', 'Artikel dihapus');
    }

    // TAMPILKAN FORM EDIT
    public function edit(Website $website, Post $post)
    {
        if ($post->website_id !== $website->id) abort(403);
        
        return view('client.posts.edit', compact('website', 'post'));
    }

    // PROSES SIMPAN PERUBAHAN
    public function update(Request $request, Website $website, Post $post)
    {
        if ($post->website_id !== $website->id) abort(403);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'title' => $request->title,
            // Slug kita update juga biar sesuai judul baru (Opsional, tapi bagus untuk SEO)
            'slug' => Str::slug($request->title) . '-' . Str::random(4), 
            'content' => $request->content,
        ];

        // Cek jika ada upload gambar baru
        if ($request->hasFile('image')) {
            // Hapus gambar lama
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }
            // Simpan gambar baru
            $data['image'] = $request->file('image')->store('posts/' . $website->id, 'public');
        }

        $post->update($data);

        return redirect()->route('client.posts.index', $website->id)->with('success', 'Artikel berhasil diperbarui');
    }
}