<?php

namespace App\Services\Port;

use App\Models\Port\Port;
use App\Models\Port\PortTerminal;
use Illuminate\Http\Request;

class PortService
{

    public function createPort(Request $request)
    {
        $port = Port::create([
            'port_type_id' => $request['port_type'],
            'name' => $request['name'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
        ]);

        // Create sales records
        $this->storeTerminals($request, $port);

        return true;
    }

    public function storeTerminals(Request $request, Port $port)
    {
        foreach ($request->input('terminals') as $portName) {
            if (!empty($portName)) {
                PortTerminal::create([
                    'name' => $portName,
                    'port_id' => $port->id
                ]);
            }
        }
    }

    public function updatePort(Request $request, $id)
    {
        try {
            $port = Port::findOrFail($id);
        } catch (\Exception $e) {
            return ['errorMsg' => "Port not found"];
        }

        // Delete existing related records
        $port->portTerminals()->delete();

        $port->update([
            'port_type_id' => $request['port_type'],
            'name' => $request['name'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
        ]);

        // Create sales records
        $this->storeTerminals($request, $port);

        return true;
    }
}
