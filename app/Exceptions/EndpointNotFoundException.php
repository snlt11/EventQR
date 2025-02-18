<?php

namespace App\Exceptions;

use Exception;

class EndpointNotFoundException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'Endpoint not found. If you are having trouble, please contact support.',
        ], 404);
    }
}
