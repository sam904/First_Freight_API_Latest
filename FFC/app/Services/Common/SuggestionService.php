<?php

namespace App\Services\Common;

use App\Models\Common\SuggestionAddress;
use App\Models\Common\SuggestionCustomer;
use Illuminate\Http\Request;

class SuggestionService
{
    public function fetchSuggestionCustomer(Request $request)
    {
        $searchTerm = $request->input('searchTerm');

        return  $customers = SuggestionCustomer::query()
            ->where('status', 'active')
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('name', 'LIKE', "%{$searchTerm}%");
            })
            ->select('id', 'name') // Adjust fields as needed
            ->get();
    }

    public function createSuggestionCustomer(Request $request)
    {
        SuggestionCustomer::create([
            'name' => $request['name'],
        ]);
        return true;
    }

    public function fetchSuggestionAddress(Request $request)
    {
        $searchTerm = $request->input('searchTerm');

        return  $customers = SuggestionAddress::query()
            ->where('status', 'active')
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('address', 'LIKE', "%{$searchTerm}%");
            })
            ->select('id', 'address') // Adjust fields as needed
            ->get();
    }


    public function createSuggestionAddress(Request $request)
    {
        SuggestionAddress::create([
            'address' => $request['address'],
        ]);
        return true;
    }
}
