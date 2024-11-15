<?php

namespace App\Services\Order;

use App\Models\Order\OrderStatusMaster;
use Illuminate\Http\Request;

class OrderService
{
    public function getAllOrders(Request $request) {}

    /**
     * Order Status Master
     */
    public function getAllOrderStatus(Request $request)
    {
        return OrderStatusMaster::paginate(10);
    }

    public function createStatusMaster(Request $request)
    {
        $orderStatusMaster = OrderStatusMaster::create([
            'name' => $request['name'],
        ]);
        return $orderStatusMaster;
    }

    public function updateStatusMaster(Request $request, OrderStatusMaster $orderStatusMaster)
    {
        $orderStatusMaster->update([
            'name' => $request['name'],
        ]);
        return true;
    }
}
