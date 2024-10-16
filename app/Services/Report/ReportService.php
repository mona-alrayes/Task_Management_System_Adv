<?php

namespace App\Services\Report;
use App\Models\TaskLog;
use App\Models\TaskStatusUpdate;

class ReportService
{
  public function TaskReport(){
    
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
                'changed_at' => $statusUpdate->changed_at,
            ];
        }),
        'task_logs' => $taskLogs->map(function ($taskLog) {
            return [
                'task_id' => $taskLog->task_id,
                'field_changed' => $taskLog->field_changed,
                'old_value' => $taskLog->old_value,
                'new_value' => $taskLog->new_value,
                'changed_at' => $taskLog->changed_at,
            ];
        }),
    ];

    return $report;
  }
}
