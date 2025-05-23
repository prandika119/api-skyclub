<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }


    public function render($request, Throwable $e): JsonResponse
    {
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => "Unauthenticated",
                'errors' => "Silahkan login terlebih dahulu"
            ], 401);
        }
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'message' => "Unauthorized",
                'errors' => "Anda tidak memiliki izin untuk mengakses resource ini"
            ], 403);
        }

        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => "Validation error",
                'errors' => $e->errors()
            ], 422);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => "Not Found",
                'errors' => "Resource atau endpoint yang diminta tidak ditemukan"
            ], 404);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'message' => "Model Not Found",
                'errors' => "Data yang diminta tidak ditemukan"
            ], 404);
        }

        // Handle server errors
        if ($e instanceof \Exception) {
            return response()->json([
                'message' => "Server Error",
                'debuging_bro' => $e->getMessage(),
                'errors' => "Terjadi kesalahan pada server. Silahkan coba lagi nanti."
            ], 500);
        }

        return parent::render($request, $e);
    }
//
//    public function render($request, Throwable $e): Response
//    {
//        if ($e instanceof AuthenticationException) {
//            return response([
//                'message' => "Unauthenticated",
//                'errors' => "Silahkan login terlebih dahulu"
//            ], 401);
//        }
//        if ($e instanceof AuthorizationException) {
//            return response([
//                'message' => "Unauthorized",
//                'errors' => "Anda tidak memiliki izin untuk mengakses resource ini"
//            ], 403);
//        }
//
//        if ($e instanceof ValidationException) {
//            return response([
//                'message' => "Validation error",
//                'errors' => $e->errors()
//            ], 422);
//        }
//
//        if ($e instanceof NotFoundHttpException) {
//            return response([
//                'message' => "Not Found",
//                'errors' => "Resource atau endpoint yang diminta tidak ditemukan"
//            ], 404);
//        }
//
//        if ($e instanceof ModelNotFoundException) {
//            return response([
//                'message' => "Model Not Found",
//                'errors' => "Data yang diminta tidak ditemukan"
//            ], 404);
//        }
//
//        // Handle server errors
//        if ($e instanceof \Exception) {
//            return response([
//                'message' => "Server Error",
//                'debuging_bro' => $e->getMessage(),
//                'errors' => "Terjadi kesalahan pada server. Silahkan coba lagi nanti."
//            ], 500);
//        }
//        return parent::render($request, $e);
//    }
}
