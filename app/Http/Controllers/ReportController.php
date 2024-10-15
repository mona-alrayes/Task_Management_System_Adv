<?php

namespace App\Http\Controllers;

use App\Models\TaskLog;
use App\Models\TaskStatusUpdate;
use App\Jobs\GenerateDailyTaskReport;

class ReportController extends Controller
{
    public function dailyTaskReport()
    {
        // Fetch all status updates for today
        $statusUpdates = TaskStatusUpdate::whereDate('changed_at', today())->get();

        // Fetch all task logs (changes) for today
        $taskLogs = TaskLog::whereDate('changed_at', today())->get();

        // Format the data into a report
        $report = [
            'date' => today()->toDateString(),
            'status_updates' => $statusUpdates->map(function ($statusUpdate) {
                return [
                    'task_id' => $statusUpdate->task_id,
                    'old_status' => $statusUpdate->old_status,
                    'new_status' => $statusUpdate->new_status,
                    'changed_at' => $statusUpdate->changed_at->toDateTimeString(),
                ];
            }),
            'task_logs' => $taskLogs->map(function ($taskLog) {
                return [
                    'task_id' => $taskLog->task_id,
                    'field_changed' => $taskLog->field_changed,
                    'old_value' => $taskLog->old_value,
                    'new_value' => $taskLog->new_value,
                    'changed_at' => $taskLog->changed_at->toDateTimeString(),
                ];
            }),
        ];

        // Return the report data in the response
        return response()->json([
            'message' => 'Daily task report generated successfully.',
            'report' => $report,
        ]);
    }
}
