<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => !empty($e->getMessage()) ? $e->getMessage() : 'Record not found.'
                ], HTTP_NOT_FOUND);
            }
            //return response(view('errors.404'), 404);
            return response()->json([
                'message' => 'Record not found.'
            ], HTTP_NOT_FOUND);
        });

        $exceptions->render(function (RouteNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found.'
                ], HTTP_NOT_FOUND);
            }
            return response(view('errors.404'), 404);
        });

        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            return response()->json([
                'message' => 'This URL is no longer valid or has expired!'
            ], HTTP_FORBIDDEN);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], HTTP_UNAUTHENTICATED);
        });

        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            return response()->json([
                'message' => 'Forbidden.'
            ], HTTP_FORBIDDEN);
        });

        $exceptions->render(function (SuspiciousOperationException $e, Request $request) {
            return response()->json([
                'message' => 'Resource not found.'
            ], HTTP_NOT_FOUND);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage() ?: Response::$statusTexts[$e->getStatusCode()]
            ], $e->getStatusCode(), $e->getHeaders());
        });

        $exceptions->render(function (HttpResponseException $e, Request $request) {
            return response()->json([
                'message' => 'An error occurred.'
            ], $e->getResponse()->getStatusCode(), $e->getResponse()->headers->all());
        });

        $exceptions->render(function (LaravelValidationException $e, Request $request) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], HTTP_VALIDATION_ERROR);
        });

        $exceptions->report(function (Throwable $e) {
            // Custom reporting logic
            // For example, logging or sending to an external service like Sentry
        });

        $exceptions->dontReport(SuspiciousOperationException::class);
        
    })->create();
