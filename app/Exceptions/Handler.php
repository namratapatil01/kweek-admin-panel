<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return ApiResponse::error(
            'Validation failed',
            $exception->status,
            $exception->errors()
        );
    }

    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return ApiResponse::error('Unauthenticated.', 401);
        }

        return redirect()->guest(route('login'));
    }

    public function render($request, Throwable $e)
    {
        if (($request->expectsJson() || $request->is('api/*')) && $e instanceof HttpException) {
            return ApiResponse::error(
                $e->getMessage() ?: 'Error',
                $e->getStatusCode()
            );
        }

        return parent::render($request, $e);
    }
}
