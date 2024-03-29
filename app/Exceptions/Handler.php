<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()){
            if ($exception instanceof ModelNotFoundException){
                return response()->json(['errors' => 'Model Not Found'], 404);
            }
            if ($exception instanceof NotFoundHttpException){
                return response()->json(['errors' => 'Invalid Route'], 404);
            }
            if ($exception instanceof ThrottleRequestsException){
                return response()->json(['errors' => 'Too Many Requests'], 429);
            }
            if ($exception instanceof MethodNotAllowedHttpException){
                return response()->json(['errors' => 'Method Not Allowed'], 405);
            }
        }
        return parent::render($request, $exception);
    }
}
