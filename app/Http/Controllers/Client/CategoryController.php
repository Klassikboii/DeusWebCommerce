<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    // Tampilkan Halaman List Kategori
    public function index(Website $website)
    {
        // Ambil kategori milik website ini saja
        $categories = $website->categories()->latest()->get();
        return view('client.categories.index', compact('website', 'categories'));
    }

    // Simpan Kategori Baru
    public function store(Request $request, Website $website)
    {
        if ($website->user_id !== auth()->id()) abort(403);

        $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                // VALIDASI BARU: Cek unik hanya jika website_id-nya sama
                Rule::unique('categories')->where(function ($query) use ($website) {
                    return $query->where('website_id', $website->id);
                }),
            ],
        ]);

        $website->categories()->create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan');
    }

    // Hapus Kategori
    public function destroy(Website $website, Category $category)
    {
        // Keamanan: Pastikan kategori ini milik website yang sedang aktif
        if ($category->website_id !== $website->id) {
            abort(403);
        }

        $category->delete();
        return redirect()->back()->with('success', 'Kategori dihapus');
    }

    // MENAMPILKAN FORM EDIT
    public function edit(Website $website, Category $category)
    {
        if ($website->user_id !== auth()->id()) abort(403);
        
        return view('client.categories.edit', compact('website', 'category'));
    }

    // MENYIMPAN PERUBAHAN
    public function update(Request $request, Website $website, Category $category)
    {
        if ($website->user_id !== auth()->id()) abort(403);

        $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                // Validasi Unik (Abaikan nama kategori ini sendiri)
                \Illuminate\Validation\Rule::unique('categories')
                    ->where(function ($query) use ($website) {
                        return $query->where('website_id', $website->id);
                    })
                    ->ignore($category->id),
            ],
        ]);

        $category->update([
            'name' => $request->name,
            // Jika ada kolom lain seperti slug/deskripsi, tambahkan disini
        ]);

        return redirect()->route('client.categories.index', $website->id)
                         ->with('success', 'Kategori berhasil diperbarui!');
    }
}

