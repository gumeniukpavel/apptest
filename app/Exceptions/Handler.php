<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
// to api methods
//use  Illuminate\Auth\AuthenticationException as AuthenticationException;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
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
        // This will replace our 404 response with
        // a JSON response.
        if ($exception instanceof ModelNotFoundException)
        {
            return response()->json([
                'errors' => ['Resource not found']
            ], 404);
        }
        if ($exception instanceof ValidationException)
        {
            return response()->json([
                'errors' => $exception->errors()
            ], 400);
        }
        if ($exception instanceof NotFoundHttpException)
        {
            return response()->json([
                'errors' => 'Route not found'
            ], 500);
        }
        if ($request->isJson())
        {
            return response()->json([
                'errors' => $exception->getMessage()
            ], 500);
        }
        Log::error($exception->getMessage(), $exception->getTrace());
        // finish JSON response

        return parent::render($request, $exception);
    }

    // to api method
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }
}
