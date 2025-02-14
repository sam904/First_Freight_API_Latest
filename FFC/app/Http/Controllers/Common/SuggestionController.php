<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\Common\SuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuggestionController extends Controller
{
    protected $suggestionService;
    public function __construct(SuggestionService $suggestionService)
    {
        $this->suggestionService = $suggestionService;
    }

    public function getSuggestionCustomer(Request $request)
    {
        Log::info("*****************************");
        Log::info('Customer Suggestion Get API..');
        Log::info("*****************************");
        $customerSuggestion = $this->suggestionService->fetchSuggestionCustomer($request);
        return response()->json(['status' => true, 'data' => $customerSuggestion], 200);
    }

    public function storeSuggestionCustomer(Request $request)
    {
        DB::beginTransaction();  // Start the transaction
        try {
            $this->suggestionService->createSuggestionCustomer($request);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Customer Suggestion created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to insert customer suggestion data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert customer suggestion data',
                "error" => $e->getMessage()
            ], 400); // Return error response
        }
    }

    public function getSuggestionAddress(Request $request)
    {
        Log::info("*****************************");
        Log::info('Address Suggestion Get API..');
        Log::info("*****************************");
        $addressSuggestion = $this->suggestionService->fetchSuggestionAddress($request);
        return response()->json(['status' => true, 'data' => $addressSuggestion], 200);
    }

    public function storeSuggestionAddress(Request $request)
    {
        DB::beginTransaction();  // Start the transaction
        try {
            $this->suggestionService->createSuggestionAddress($request);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Address Suggestion created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to insert address suggestion data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert address suggestion data',
                "error" => $e->getMessage()
            ], 400); // Return error response
        }
    }
}
