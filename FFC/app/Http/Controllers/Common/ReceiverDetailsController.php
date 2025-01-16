<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\Common\ReceiverDetailService;
use Illuminate\Http\Request;

class ReceiverDetailsController extends Controller
{
    protected $receiverDetailService;
    public function __construct(ReceiverDetailService $receiverDetailService)
    {
        $this->receiverDetailService = $receiverDetailService;
    }

    public function getReceiverName(Request $request) {}

    public function storeReceiverName(Request $request) {}

    public function getReceiverAddress(Request $request) {}

    public function storeReceiverAddress(Request $request) {}
}
