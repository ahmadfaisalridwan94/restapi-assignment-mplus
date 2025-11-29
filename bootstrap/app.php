<?php

use App\Providers\RepositoryServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Auth\AuthenticationException;

use Tymon\JWTAuth\Http\Middleware\Authenticate as JWTAuthMiddleware;
use Tymon\JWTAuth\Http\Middleware\RefreshToken as JWTRefreshMiddleware;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;



return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => JWTAuthMiddleware::class,
            'jwt.refresh' => JWTRefreshMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (UnauthorizedHttpException $e, $request) {
            if ($request->is('api/*')) {
                // return response()->json([
                //     'status' => false,
                //     'message' => 'Unauthenticated'
                // ], 401);

                // Ambil detail error JWT
                $prev = $e->getPrevious();
                $message = $prev ? $prev->getMessage() : 'Unauthorized';

                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 401);
            }
        });

        $exceptions->render(function (TokenInvalidException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token is invalid'
                ], 401);
            }
        });

        $exceptions->render(function (TokenExpiredException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token has expired'
                ], 401);
            }
        });

        $exceptions->render(function (JWTException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token not provided'
                ], 401);
            }
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found: ' . $e->getMessage(),
                ], 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Method not allowed',
                ], 405);
            }
        });

        $exceptions->render(function (RouteNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Route not found: ' . $e->getMessage(),
                ], 404);
            }
        });
    })->create();
