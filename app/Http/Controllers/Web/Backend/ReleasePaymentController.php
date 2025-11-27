<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ReleasePaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            Artisan::call('process:instructor-payouts');
            return response()->json(['status' => 'success', 'message' => 'Payment has been released.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to initiate payment release.']);
        }
    }
}
