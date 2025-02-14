<?php

namespace App\Services\Common;

use App\Models\Common\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeService
{

    public function createServiceType(Request $request)
    {
        ServiceType::create([
            'name' => $request['name'],
            'sort_level' => $request['sortLevel'],
        ]);
        return true;
    }
    public function updateServiceType(Request $request, ServiceType $serviceType)
    {
        $serviceType->update([
            'name' => $request['name'],
            'sort_level' => $request['sortLevel'],
        ]);
        return true;
    }
}
