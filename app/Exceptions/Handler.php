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
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // Check if the exception is an instance of ModelNotFoundException
        if ($exception instanceof ModelNotFoundException) {
            Log::error("Model not found. Error: " . $exception->getMessage());
            return response()->json(['message'=>'الموديل غير موجود'], 404);
        }
        if($exception instanceof RelationNotFoundException) {
            Log::error("Relation not found. Error: " . $exception->getMessage());
            return response()->json(['message'=>'العلاقة غير موجودة'], 404);
        }
        if($exception instanceof Exception) {
            Log::error("Error Happened : " . $exception->getMessage());
            return response()->json(['message'=>'حدث خطأ في المخدم'], 500);
        }

        // For other exceptions, call the parent render method
         return parent::render($request, $exception);
    }
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
        parent::report($exception);
     }
}
