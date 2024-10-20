<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header("Authorization");
        $auth = true;

        if (!$token) {
            $auth = false;
        }

        $user = User::where("token", $token)->first();
        if (!$user) {
            $auth = false;
        } else {
            Auth::login($user);
        }
        if ($auth) {
            return $next($request);
        }
        return response()->json([
            "errors" => ["message" => ["unauthorized"]]
        ])->setStatusCode(401);
    }
}
