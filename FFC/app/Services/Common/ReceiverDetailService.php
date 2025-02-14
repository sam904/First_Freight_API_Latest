<?php

namespace App\Services\Common;

use App\Models\Common\ReceiverAddress;
use App\Models\Common\ReceiverDetails;
use App\Models\Common\ReceiverName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiverDetailService
{
    public function fetchReceiverNameDetails(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        return ReceiverName::query()
            ->where('status', 'active')
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('name', 'LIKE', "%{$searchTerm}%");
            })
            ->select('id', 'name')
            ->get();
    }

    public function createReceiverName(Request $request)
    {
        ReceiverName::create([
            'name' => $request['name']
        ]);
        return true;
    }

    public function fetchReceiverAddressDetails(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        return ReceiverAddress::query()
            ->where('status', 'active')
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('address', 'LIKE', "%{$searchTerm}%");
            })
            ->select('id', 'address')
            ->get();
    }

    public function createReceiverAddress(Request $request)
    {
        ReceiverAddress::create([
            'address' => $request['address']
        ]);
        return true;
    }
}
