<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

// Rute Webhook Midtrans
// URL aslinya nanti akan menjadi: domainanda.com/api/midtrans/webhook
Route::post('/midtrans/webhook', [WebhookController::class, 'handleMidtrans']);