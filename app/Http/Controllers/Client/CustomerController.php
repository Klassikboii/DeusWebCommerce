<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);

        // LOGIKA: Ambil data order, kelompokkan berdasarkan No WA yang sama.
        // Kita hitung juga berapa kali dia order dan total belanjanya.
        
        $customers = $website->orders()
            ->select(
                'customer_whatsapp', 
                'customer_name', 
                'customer_address',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('MAX(created_at) as last_order')
            )
            ->groupBy('customer_whatsapp', 'customer_name', 'customer_address')
            ->orderByDesc('last_order')
            ->paginate(10);

        return view('client.customers.index', compact('website', 'customers'));
    }
}