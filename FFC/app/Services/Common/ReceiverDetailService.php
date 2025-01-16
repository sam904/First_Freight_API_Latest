<?php

namespace App\Services\Common;

use App\Models\Common\ReceiverDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiverDetailService
{
    public function fetchSuggestionCustomer(Request $request)
    {
        $searchTerm = $request->input('searchTerm');

        return  $customers = ReceiverDetails::query()
            ->where('status', 'active')
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('name', 'LIKE', "%{$searchTerm}%");
            })
            ->select('id', 'name') // Adjust fields as needed
            ->get();
    }

    public function createSuggestionCustomer(Request $request)
    {
        ReceiverDetails::create([
            'name' => $request['name'],
        ]);
        return true;
    }
}
