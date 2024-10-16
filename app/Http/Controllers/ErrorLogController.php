<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;

class ErrorLogController extends Controller
{
    public function index()
    {
        // Retrieve all error logs, you can paginate if needed
        $errorLogs = ErrorLog::orderBy('created_at', 'desc')->paginate(10);

        // Return the error logs as JSON response 
        return self::paginated($errorLogs , 'errors retrevied successfully', 200);
    }
}
