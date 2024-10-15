<?php

namespace App\Jobs;

use App\Models\TaskLog;
use Illuminate\Bus\Queueable;
use App\Models\TaskStatusUpdate;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateDailyTaskReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
         // Fetch all status updates for today
         $statusUpdates = TaskStatusUpdate::whereDate('changed_at', today())->get();

         // Fetch all task logs (changes) for today
         $taskLogs = TaskLog::whereDate('changed_at', today())->get();
 
         // Example report generation logic:
         Log::info("Daily Report:");
         Log::info("Status Updates Today: " . $statusUpdates->count());
         Log::info("Task Logs (All Changes) Today: " . $taskLogs->count());
 
    }
}
