<?php

namespace App\Http\Controllers;

use App\Services\Report\ReportService;

class ReportController extends Controller
{

    protected ReportService $ReportService;

    public function __construct(ReportService $ReportService)
    {
        $this->ReportService = $ReportService;
    }
    public function dailyTaskReport()
    {
        $taskReport = $this->ReportService->TaskReport();
        return self::Success($taskReport , 'Daily task report generated successfully.' , 200);
    }
}
