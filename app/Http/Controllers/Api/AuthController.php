<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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
        $email = $request->get('email');
        $password = $request->get('password');

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user();
            if (!$user->api_token) {
                $user->api_token = Str::random(60);
                $user->update();
            }
            return response()->json(['api_token' => $user->api_token], Response::HTTP_OK);
        }
        return response()->json(['message' => 'Email or password is incorrect.'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Logout from system
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $user = User::where('api_token', $request->header('Authorization'))->first();
        $user->api_token = '';
        $user->update();
        return response()->json(['message' => 'Logout successfully'], Response::HTTP_OK);
    }
}
