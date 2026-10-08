<?php
use App\Http\Controllers\WebhookReceiverController; use Illuminate\Support\Facades\Route;
Route::match(['get','post','put','patch','delete'],'/{endpoint:token}',WebhookReceiverController::class)->middleware('throttle:webhook')->name('webhook.receive');