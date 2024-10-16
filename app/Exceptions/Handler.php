<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use App\Models\ErrorLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $exception)
    {
        // Log specific exceptions
        // if (
        //     $exception instanceof ModelNotFoundException ||
        //     $exception instanceof NotFoundHttpException ||
        //     $exception instanceof RelationNotFoundException ||
        //     $exception instanceof Throwable
        // ) {

            try {
                ErrorLog::create([
                    'exception_type' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'input' => json_encode(request()->all()),
                ]);
            } catch (Exception $e) {
                Log::error('Failed to log exception: ' . $e->getMessage());
            }
        // }

        // Continue with normal exception reporting
        parent::report($exception);
     }
}
