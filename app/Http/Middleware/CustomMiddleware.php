<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use App\Helper\MessageError;

class CustomMiddleware
{
    protected $maxAttempts = 70;
    protected $decayMinutes = 1;
    protected $banAttempts = 100;
    protected $banMinutes = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $key = 'throttle_' . $request->ip();
        $banKey = 'ban_' . $request->ip();

        if (RateLimiter::tooManyAttempts($banKey, $this->banAttempts)) {
            $seconds = RateLimiter::availableIn($banKey);
            return response()->json([
                'message' => 'You have been temporarily banned due to excessive requests. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 403);
        }

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            RateLimiter::hit($banKey, $this->banMinutes * 60);
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
            ], 429);
        }

        RateLimiter::hit($key, $this->decayMinutes * 60);

        $response = $next($request);

        if ($response->getStatusCode() === 404) {
            throw new MessageError('Endpoint not found. If you are having trouble, please contact support.', 404);
        }

        return $response;
    }
}
