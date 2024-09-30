
<?php

if (!function_exists('statusUpdate')) {
    /**
     * Find a model by ID and update it with the provided data.
     *
     * @param string $modelClass The model class (e.g., \App\Models\Vendor::class).
     * @param mixed $id The ID of the model to find.
     * @param array $data The data to update in the model.
     * @return \Illuminate\Http\JsonResponse
     */
    function statusUpdate($modelClass, $id, $data)
    {
        try {
            // Find the model by ID
            $model = $modelClass::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => class_basename($modelClass) . ' not found'
            ], 404);
        }

        // Update the model with the provided data
        $model->update($data);

        return response()->json([
            'status' => true,
            'message' => class_basename($modelClass) . ' status updated successfully'
        ], 200);
    }
}

if (!function_exists('findModel')) {
    /**
     * Find a model by its ID and return it. Handle the ModelNotFoundException gracefully.
     *
     * @param string $modelClass The model class (e.g., \App\Models\Vendor::class).
     * @param mixed $id The ID of the model to find.
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Http\JsonResponse
     */
    function findModel($modelClass, $id)
    {
        try {
            // Attempt to find the model by its ID
            return $modelClass::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Return a JSON response if the model is not found
            return response()->json([
                'status' => false,
                'message' => class_basename($modelClass) . ' not found'
            ], 404);
        }
    }
}
