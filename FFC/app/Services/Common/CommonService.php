<?php

namespace App\Services\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommonService
{
    public function uploadDocuments(Request $request)
    {
        $uploadedImages = [];
        $destinationPath = 'images/upload/';
        $images = $request->file('uploadDocuments');

        if (is_array($images)) {
            foreach ($images as $image) {
                Log::info("In case of Multiple image input");
                $imgName = date('YmdHis') . str_replace('.', '', microtime(true)) . "." . $image->getClientOriginalExtension();
                $image->move($destinationPath, $imgName);
                $uploadedImages[] = $destinationPath . $imgName; // Collect only the file names
            }
        } elseif ($images) {
            Log::info("In case of single image input");
            $imgName = date('YmdHis') . str_replace('.', '', microtime(true)) . "." . $images->getClientOriginalExtension();
            $images->move($destinationPath, $imgName);
            $uploadedImages[] = $destinationPath . $imgName;
        }
        Log::info("All Images path => " . implode(',', $uploadedImages));
        return $uploadedImages;
    }
}
