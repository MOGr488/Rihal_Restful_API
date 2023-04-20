<?php

namespace App\Exceptions;


use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

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
        if ($exception instanceof AuthenticationException) {
            return  new JsonResponse([
                'error' => [
                    'message' => 'Unauthenticated!'
                ]
            ], 401);
        }
       /* if ($exception instanceof AuthorizationException) {
            return  new JsonResponse([
                 'error' => [
                     'message' => 'Not Authorized to Perform this Action!'
                 ]
             ], 403);
         } */
        if ($exception instanceof ModelNotFoundException) {
            return  new JsonResponse([
                'error' => [
                    'message' => 'Not Found!'
                ]
            ], 404);
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return  new JsonResponse([
                 'error' => [
                     'message' => 'Request method is not Supported for this route!'
                 ]
             ], 405);
         }
         /*else{
            return  new JsonResponse([
                'error' => [
                    'message' => 'Route is not support or you do not have permission to access this route! Make sure to use the latest API version.'
                ]
            ], 404);
         }*/
        return parent::render($request, $exception);
    }
}
