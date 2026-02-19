<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request, Website $website)
    {
        // KITA MEMBUAT "VIRTUAL CUSTOMER" DARI TABEL ORDER
        
        // 1. Base Query: Ambil dari tabel orders
        $query = $website->orders()
            ->select('customer_name', 'customer_address', 'customer_whatsapp') // Pilih kolom identitas
            ->selectRaw('MAX(created_at) as last_order_date') // Kapan terakhir order
            ->selectRaw('COUNT(*) as total_orders') // Berapa kali order
            ->selectRaw('SUM(total_amount) as total_spent') // Total uang yang dibelanjakan
            ->groupBy('customer_address', 'customer_name', 'customer_whatsapp'); // Grouping biar unik

        // 2. Logika Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_address', 'like', '%' . $search . '%')
                  ->orWhere('customer_whatsapp', 'like', '%' . $search . '%');
            });
        }

        // 3. Pagination
        // Perlu diingat: Paginating group by results kadang tricky di Laravel, 
        // tapi untuk MySQL modern ini biasanya aman.
        $customers = $query->latest('last_order_date')->paginate(10)->withQueryString();

        return view('client.customers.index', compact('website', 'customers'));
    }
}