<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\Common\ReceiverDetailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReceiverDetailsController extends Controller
{
    protected $receiverDetailService;
    public function __construct(ReceiverDetailService $receiverDetailService)
    {
        $this->receiverDetailService = $receiverDetailService;
    }

    public function getReceiverName(Request $request)
    {
        $receiverName = $this->receiverDetailService->fetchReceiverNameDetails($request);
        return response()->json([
            'status' => true,
            'data' => $receiverName
        ], 201);
    }

    public function storeReceiverName(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $this->receiverDetailService->createReceiverName($request);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Receiver Name created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert receiver name data',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getReceiverAddress(Request $request)
    {
        $receiverName = $this->receiverDetailService->fetchReceiverAddressDetails($request);
        return response()->json([
            'status' => true,
            'data' => $receiverName
        ], 201);
    }

    public function storeReceiverAddress(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'address' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $this->receiverDetailService->createReceiverAddress($request);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Receiver Address created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert receiver address data',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
