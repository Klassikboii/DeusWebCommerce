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
}