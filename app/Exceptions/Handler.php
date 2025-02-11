<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
        $this->renderable(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            return response()->json([
                'message' => 'You are not authorized to perform this action'
            ], 403);
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            return response()->json([
                'message' => 'You are not authorized to perform this action'
            ], 403);
        });

        $this->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            $modelName = strtolower(class_basename($e->getModel()));
            return response()->json([
                'message' => "The requested {$modelName} was not found"
            ], 404);
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            // Check if the exception was caused by model binding
            if ($previous = $e->getPrevious()) {
                if ($previous instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $modelName = strtolower(class_basename($previous->getModel()));
                    return response()->json([
                        'message' => "The requested {$modelName} was not found"
                    ], 404);
                }
            }
            
            return response()->json([
                'message' => 'The requested resource was not found'
            ], 404);
        });
    }
}
