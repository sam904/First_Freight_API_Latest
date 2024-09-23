<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomoerController extends Controller
{
    public function index(Request $request)
    {
        $customer = Customer::paginate(10);
        return response()->json($customer);
    }
}
