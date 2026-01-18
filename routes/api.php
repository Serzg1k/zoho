<?php

use App\Http\Controllers\Api\ZohoFormController;
use Illuminate\Support\Facades\Route;

Route::post('/zoho/submit', [ZohoFormController::class, 'submit']);
