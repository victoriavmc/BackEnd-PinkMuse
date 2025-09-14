<?php

namespace App\Traits;

trait ApiResponse
{
    public function success($data, $message = 'Operación exitosa', $code = 200)
    {
        return response()->json([
            'status' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function error($message = 'Ocurrió un error', $code = 400, $errors = [])
    {
        return response()->json([
            'status' => $code,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
