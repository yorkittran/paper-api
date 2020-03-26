<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        $user = User::where('email', $request->get('email'))->first();
        $user->update($request->only('push_token'));
        return response()->json(['token' => $token, 'role' => constants('user.role.' . $user->role)], Response::HTTP_OK);
    }

    /**
     * Logout from system
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user->push_token = null;
        $user->update();
        return Response::HTTP_NO_CONTENT;
    }
}
