<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Validator;

//models
use App\Models\User;

// packages
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return ResponseHelper::jsonResponse(false, '0001', 'validation error', $validator->errors(), 422);
        }

        //get credentials from request
        $credentials = $request->only('email', 'password');

        //if auth failed
        if (!$token = JWTAuth::attempt($credentials)) {
            return ResponseHelper::jsonResponse(false, '0002', 'Unauthorized', $validator->errors(), 401);
        }

        //if auth success
        return ResponseHelper::jsonResponse(true, '0000', 'Login success', [
            'user' => auth()->user(),
            'token' => $token
        ], 200);
    }

    public function register(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8|confirmed'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return ResponseHelper::jsonResponse(false, '0001', 'validation error', $validator->errors(), 422);
        }

        //create user
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password)
        ]);

        //return response JSON user is created
        if ($user) {
            return ResponseHelper::jsonResponse(true, '0000', 'registration success', $user, 201);
        }

        //return JSON process insert failed
        return ResponseHelper::jsonResponse(false, '0002', 'registration failed', $user, 409);
    }

    public function google(Request $request)
    {
        return "google auth";
    }

    public function facebook(Request $request)
    {
        return "facebook auth";
    }

    public function logout()
    {
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());
        if ($removeToken) {
            return ResponseHelper::jsonResponse(true, '0000', 'Logout success', [], 200);
        }
    }

    public function refresh()
    {
        try {
            $newToken = auth()->refresh(true, true);

            return response()->json([
                'status' => true,
                'message' => 'Token refreshed successfully',
                'token' => $newToken
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token is invalid'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to refresh token'
            ], 500);
        }
    }
}
