<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\StringHelper;
use App\Http\Requests\GoogleLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
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

    public function register(RegisterRequest $request)
    {
        //create user
        $user = User::create([
            'name'      => $request->name,
            'username'     => StringHelper::generateUniqueUsername($request->name),
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

    public function generateFacebookAuthUrl()
    {
        $url = Socialite::driver('facebook')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return ResponseHelper::jsonResponse(true, '0000', 'Success', [
            'url' => $url
        ], 200);
    }

    public function google(GoogleLoginRequest $request)
    {

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

        $user = User::where('email', $googleUser['email'])->first();
        if ($user) {
            return ResponseHelper::jsonResponse(true, '0000', 'Success', [
                'user' => $user,
                'token' => JWTAuth::fromUser($user)
            ], 200);
        }

        $user = User::updateOrCreate(
            ['email' => $googleUser['email']],
            [
                'email' => $googleUser['email'],
                'username' => StringHelper::generateUniqueUsername($googleUser['name']),
                'name' => $googleUser['name'],
                'avatar' => $googleUser['picture'],
            ]
        );

        return ResponseHelper::jsonResponse(true, '0000', 'Success', [
            'user' => $user,
            'token' => JWTAuth::fromUser($user)
        ], 200);
    }

    public function facebook(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $tokenData = Http::asForm()->get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'redirect_uri' => config('services.facebook.redirect'),
                'code' => $request->code,
            ])->json();

            $facebookUser = Socialite::driver('facebook')
                ->stateless()
                ->userFromToken($tokenData['access_token']);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, '0003', 'Facebook auth failed', [], 401);
        }

        $user = User::where('email', $facebookUser['email'])->first();
        if ($user) {
            return ResponseHelper::jsonResponse(true, '0000', 'Success', [
                'user' => $user,
                'token' => JWTAuth::fromUser($user)
            ], 200);
        }

        $user = User::updateOrCreate(
            ['email' => $facebookUser->email],
            [
                'name' => $facebookUser->name,
                'username' => StringHelper::generateUniqueUsername($facebookUser['name']),
                'email' => $facebookUser->email,
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
}
