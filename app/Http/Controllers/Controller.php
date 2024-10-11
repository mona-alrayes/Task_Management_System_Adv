<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * sucess function
     *
     * @param [type] $data
     * @param string $message
     * @param integer $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = null, $message = 'Done Successfully!', $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => trans($message),
            'data' => $data,
        ], $status);
    }

    /**
     * error function
     *
     * @param [type] $data
     * @param string $message
     * @param integer $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($data = null, $message = 'Operation failed!', $status = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => trans($message),
            'data' => $data,
        ], $status);
    }

    /**
     * pagination function
     *
     * @param LengthAwarePaginator $paginator
     * @param string $message
     * @param [type] $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function paginated(LengthAwarePaginator $paginator, $message = '', $status): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => trans($message),
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
        ], $status);
    }
}
