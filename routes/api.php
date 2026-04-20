<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

// Rute Webhook Midtrans
// URL aslinya nanti akan menjadi: domainanda.com/api/midtrans/webhook
Route::post('/midtrans/webhook', [WebhookController::class, 'handlePaymentWebhook']);

// Di dalam file routes/api.php
Route::post('/webhooks/accurate', [\App\Http\Controllers\WebhookController::class, 'handleAccurateWebhook']);

// Rute Webhook Pivot (Baru)
Route::post('/pivot/webhook', [WebhookController::class, 'handlePivotWebhook']);