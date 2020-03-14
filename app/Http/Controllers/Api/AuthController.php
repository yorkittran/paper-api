<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Str;

class AuthController extends Controller
{
    /**
     * Login to system
     *
     * @param  \App\Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Email or password is incorrect.'], Response::HTTP_UNAUTHORIZED);
        }
        return response()->json(['token' => $token], Response::HTTP_OK);
    }

    /**
     * Logout from system
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {

    }
}
