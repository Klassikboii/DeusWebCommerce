<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request, Website $website)
    {
        $query = $website->customers()

            ->leftJoin('orders', function ($join) {
                $join->on('customers.id', '=', 'orders.customer_id')
                      ->whereIn('orders.status', ['shipped', 'processing', 'completed']);
            })

            ->select(
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.whatsapp',
                'customers.created_at'
            )

            ->selectRaw('COUNT(orders.id) as total_orders')

            ->selectRaw('
                COALESCE(SUM(orders.total_amount), 0) as total_spent
            ')

            ->selectRaw('
                MAX(orders.created_at) as last_order_date
            ')

            ->groupBy(
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.whatsapp',
                'customers.created_at'
            );

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'like', "%{$search}%")
                  ->orWhere('customers.email', 'like', "%{$search}%")
                  ->orWhere('customers.whatsapp', 'like', "%{$search}%");
            });
        }

        $customers = $query
            ->latest('last_order_date')
            ->paginate(10)
            ->withQueryString();

        return view('client.customers.index', compact(
            'website',
            'customers'
        ));
    }
}