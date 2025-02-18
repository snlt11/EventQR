<?php

namespace App\Helper;

use Throwable;
use Exception;

class MessageError extends Exception implements Throwable
{
    public function render($request)
    {
        return response()->json(['message' => $this->getMessage()], 404);
    }
}
