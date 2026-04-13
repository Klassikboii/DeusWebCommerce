<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PivotService implements PaymentGatewayInterface
{
    protected $website;

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    public function createTransaction(Order $order): array
    {
        // Contoh: Tembak API Pivot
        // $response = Http::withToken($this->website->pivot_api_key)->post('api.pivot.id/v1/invoice', [...]);
        
        return [
            'redirect_url' => $response['payment_url'],
            'token' => null 
        ];
    }

    public function handleWebhook(Request $request): array
    {
        // Logika baca webhook Pivot
        return [
            'order_id' => $request->reference_no, 
            'status' => $request->payment_status === 'SUCCESS' ? 'paid' : 'failed',
            'raw_response' => $request->all()
        ];
    }
}