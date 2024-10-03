
<?php

use Illuminate\Support\Str;

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



if (!function_exists('encryptPassword')) {
    function encryptPassword($password, $key)
    {
        $cipher = "aes-256-cbc"; // Encryption cipher

        // Generate an IV (16 bytes for AES-256-CBC)
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher)); // Generate an IV

        // Encrypt the password
        $encryptedPassword = openssl_encrypt($password, $cipher, $key, 0, $iv);

        // Return the encrypted password and IV (which is needed for decryption)
        return base64_encode($encryptedPassword . "::" . $iv);
    }
}

if (!function_exists('decryptPassword')) {
    function decryptPassword($encryptedPassword, $key)
    {
        $cipher = "aes-256-cbc"; // Encryption cipher

        // Split the encrypted password into the actual encrypted data and the IV
        list($encrypted_data, $iv) = explode("::", base64_decode($encryptedPassword), 2);

        // Decrypt the password
        return openssl_decrypt($encrypted_data, $cipher, $key, 0, $iv);
    }
}

if (!function_exists('generateSecretKey')) {
    function generateSecretKey($length = 32)
    {
        return Str::random($length);
    }
}
