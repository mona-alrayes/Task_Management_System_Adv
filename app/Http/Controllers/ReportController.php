<?php

namespace App\Http\Controllers;

use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    protected ReportService $reportService; // Changed to camelCase for consistency

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate the daily task report.
     *
     * @return JsonResponse
     */
    public function dailyTaskReport(): JsonResponse
    {
        // Retrieve the daily task report
        $taskReport = $this->reportService->TaskReport();

        // Return the report with a success message
        return self::success($taskReport, 'Daily task report generated successfully.', 200);
    }
}
