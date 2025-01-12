<?php

namespace App\Services\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommonService
{
    public function uploadDocuments(Request $request)
    {
        Log::info("Start uploading images...");
        Log::info($request->input('overwrite'));
        $uploadedImages = [];
        $destinationPath = 'images/upload/';
        $images = $request->file('uploadDocuments');

        // Collect image names and validate first
        $imageNames = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                $imageNames[] = $image->getClientOriginalName();
            }
        } elseif ($images) {
            $imageNames[] = $images->getClientOriginalName();
        }

        // Check if any of the files already exist
        $existingImages = [];
        foreach ($imageNames as $imgName) {
            if (file_exists($destinationPath . $imgName)) {
                $existingImages[] = $imgName;
            }
        }

        // Handle overwrite condition
        // if (!$request->input('overwrite', false)) {
        if (!empty($existingImages)) {
            throw new \Exception("File(s) already exist: " . implode(', ', $existingImages));
        }
        // }

        // If validation passes, upload all images
        if (is_array($images)) {
            foreach ($images as $image) {
                Log::info("Uploading image: " . $image->getClientOriginalName());
                $imgName = $image->getClientOriginalName();
                $image->move($destinationPath, $imgName);
                $uploadedImages[] = $destinationPath . $imgName;
            }
        } elseif ($images) {
            Log::info("Uploading single image: " . $images->getClientOriginalName());
            $imgName = $images->getClientOriginalName();
            $images->move($destinationPath, $imgName);
            $uploadedImages[] = $destinationPath . $imgName;
        }

        // Log and return success response
        Log::info("All Images path => " . implode(',', $uploadedImages));
        return $uploadedImages;
    }
}
