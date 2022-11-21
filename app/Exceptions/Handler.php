<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // If use Accept: application/json in request
        if ($request->expectsJson()) {
            return $this->customApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    private function customApiException(Request $request, Throwable $exception)
    {
        $message = $exception->getMessage();
        $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($exception instanceof NotFoundHttpException) {
            $message = "HTTP NOT FOUNT";
            $responseCode = Response::HTTP_NOT_FOUND;
        } elseif ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $message = 'По данному запросу не найдена запись в модели '. $exception->getModel();
            $responseCode = Response::HTTP_NOT_FOUND;
        }elseif ($exception instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
            $responseCode = Response::HTTP_NOT_FOUND;
        }elseif($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $responseCode = Response::HTTP_UNAUTHORIZED;
        }elseif ($exception instanceof \Illuminate\Validation\ValidationException) {
            $responseCode = Response::HTTP_BAD_REQUEST;
        }elseif ($exception instanceof \Illuminate\Database\QueryException) {
            $message = $exception->errorInfo[2] ?? $exception->getMessage();
            $responseCode = Response::HTTP_BAD_REQUEST;
        }

        return response()->json([
            'message' => $message,
        ], $responseCode);
    }
}
