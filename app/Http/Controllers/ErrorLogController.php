<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use Illuminate\Http\JsonResponse;

class ErrorLogController extends Controller
{
    /**
     * Display a listing of the error logs.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Retrieve all error logs, ordered by created_at in descending order
        $errorLogs = ErrorLog::orderBy('created_at', 'desc')->paginate(10);

        // Return the error logs paginated
        return self::paginated($errorLogs, 'Errors retrieved successfully', 200);
    }
}
