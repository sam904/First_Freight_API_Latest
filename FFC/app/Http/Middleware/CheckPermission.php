<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $master, $action)
    {
        $allowedMasters = [
            'User',
            'Vendor',
            'Customer',
            'Port',
            'Destination',
            'Rate',
            'RateNote',
            'Quote',
            'QuoteNote',
            'Order',
        ]; // Add valid master names here.
        if (!in_array($master, $allowedMasters)) {
            return response()->json(['error' => 'Invalid master name for permission'], 400);
        }

        // Fetch master_id securely.
        $masterId = DB::table('masters')->where('name', $master)->value('id');
        if (!$masterId) {
            return response()->json(['error' => 'Master not found for permission'], 404);
        }

        $user = Auth::user(); // Get the logged-in user ID.
        Log::info("Checking Permission for user =>" . $user->id . " && master id=>" . $master);
        $permission = Permission::where('user_id', $user->id)
            ->where('master_id', $masterId)
            ->value($action);

        if (!$permission) {
            return response()->json([
                'status' => false,
                'error' => 'You do not have permission to perform this action.'
            ], 403);
        }

        return $next($request);
    }
}
