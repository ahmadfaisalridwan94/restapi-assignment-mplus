<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;

//models
use App\Models\User;
use Illuminate\Support\Facades\Http;
// packages
use Tymon\JWTAuth\Facades\JWTAuth;
use Laravel\Socialite\Socialite;
use Pest\Support\Str;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {

        //get credentials from request
        $credentials = $request->only('email', 'password');

        //if auth failed
        if (!$token = JWTAuth::attempt($credentials)) {
            return ResponseHelper::jsonResponse(false, '0002', 'Unauthorized', [], 401);
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
            'username'     => $request->email,
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

    public function generateGoogleAuthUrl()
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');
        $codeVerifier = Str::random(64);
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        return ResponseHelper::jsonResponse(true, '0000', 'Success', [
            'auth_url' => $authUrl,
            'code_verifier' => $codeVerifier,
        ], 200);
    }

    public function google(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'code_verifier' => 'required|string',
        ]);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $request->code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect'),
            'grant_type' => 'authorization_code',
            'code_verifier' => $request->code_verifier,
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Invalid Google code',
                'details' => $response->json()
            ], 400);
        }

        $tokenData = $response->json();

        if (!isset($tokenData['access_token'])) {
            return response()->json([
                'error' => 'Google did not return an access token',
                'details' => $tokenData,
            ], 400);
        }

        $accessToken = $tokenData['access_token'];

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($accessToken);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch Google user info',
                'details' => $e->getMessage(),
            ], 400);
        }

        $user = User::updateOrCreate(
            ['email' => $googleUser['email']],
            [
                'username' => $googleUser['email'],
                'name' => $googleUser['name'],
                'avatar' => $googleUser['picture'],
            ]
        );

        return ResponseHelper::jsonResponse(true, '0000', 'Success', [
            'user' => $user,
            'token' => JWTAuth::fromUser($user)
        ], 200);
    }

    public function facebook()
    {

        dd(get_class_methods(Socialite::driver('google')));
        $accessToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcmVzdGFwaS50ZXN0L2FwaS92MS9hdXRoL2dvb2dsZSIsImlhdCI6MTc2NDM5MDIxNSwiZXhwIjoxNzY0MzkzODE1LCJuYmYiOjE3NjQzOTAyMTUsImp0aSI6Img5Zml1WUpDcHliR2dxUW0iLCJzdWIiOiJhZWVjNmI5OS1mNDczLTQxYjQtYjlhMC1kNTZhYjVhZDIwYzMiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.qmoAlWYpsWqc_1qIkWvMicJq4ZkQXFmXd7NPhOwgmUA";
        $user = Socialite::driver('google')->userFromToken($accessToken);
        dd($user);


        $url = Socialite::driver('facebook')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return ResponseHelper::jsonResponse(true, '0000', 'Success', [
            'url' => $url
        ], 200);
    }

    public function facebook_callback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, '0003', 'Facebook auth failed', [], 401);
        }

        $user = User::updateOrCreate(
            ['email' => $facebookUser->email],
            [
                'name' => $facebookUser->name,
                'provider_name' => 'facebook',
                'provider_id' => $facebookUser->id,
                'avatar' => $facebookUser->avatar
            ]
        );

        return ResponseHelper::jsonResponse(true, '0000', 'Success', [
            'user' => $user,
            'token' => JWTAuth::fromUser($user)
        ], 200);
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
            return ResponseHelper::jsonResponse(true, '0000', 'Token refreshed successfully', ['token' => $newToken], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ResponseHelper::jsonResponse(false, '0003', 'Token is invalid', [], 401);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, '0003', 'Unable to refresh token', [], 500);
        }
    }

    public function check()
    {
        // get header
        $token = request()->header('X-TOKEN');

        $user = User::where('social_auth_request_token', $token)->update(['social_auth_request_token' => null]);

        // check if login
        if ($user = JWTAuth::parseToken()->authenticate()) {
            return ResponseHelper::jsonResponse(true, '0000', 'Success', ['user' => $user], 200);
        }
    }
}
