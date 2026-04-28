<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAccountController extends Controller
{
    public function index(Request $request)
    {
        $website = $request->get('website');
        $customer = Auth::guard('customer')->user(); // Ambil data pembeli yang sedang login

        // Tarik semua pesanan milik pembeli ini di toko ini, urutkan dari yang paling baru
        $orders = Order::where('website_id', $website->id)
                       ->where('customer_id', $customer->id)
                       ->orderBy('created_at', 'desc')
                       ->get();

        return view('storefront.account', compact('website', 'customer', 'orders'));
    }
    public function edit(Request $request)
{
    $website = $request->get('website');
    $customer = Auth::guard('customer')->user();
    return view('storefront.profile', compact('website', 'customer'));
}

public function update(Request $request)
{
    $website = $request->get('website');
    $customer = Auth::guard('customer')->user();

    $request->validate([
        'name' => 'required|string|max:100',
        'email' => 'required|email|unique:customers,email,' . $customer->id . ',id,website_id,' . $website->id,
        'whatsapp' => 'required|numeric|unique:customers,whatsapp,' . $customer->id . ',id,website_id,' . $website->id,
        'password' => 'nullable|min:6|confirmed',
    ]);

    $data = [
        'name' => $request->name,
        'email' => $request->email,
        'whatsapp' => $request->whatsapp,
    ];

    // Jika password diisi, maka update password
    if ($request->filled('password')) {
        $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
    }

    $customer->update($data);

    return redirect()->route('store.profile.edit')->with('success', 'Profil Anda berhasil diperbarui.');
}
}