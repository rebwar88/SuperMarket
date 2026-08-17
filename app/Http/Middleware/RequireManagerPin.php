<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireManagerPin
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
