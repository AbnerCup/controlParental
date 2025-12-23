<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceGatewayController;

Route::post('/gateway/device-events', [DeviceGatewayController::class, 'ingest']);
