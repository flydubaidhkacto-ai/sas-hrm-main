<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'Billing controller is available.',
        ]);
    }
}
