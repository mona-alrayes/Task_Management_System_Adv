<?php

namespace App\Services\Task;

use Exception;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;


class TaskService
{
    public function getTasks($request): LengthAwarePaginator
    {
        try {
            $tasks = Cache::remember('tasks', 3600, function () use ($request) {
                return Task::query()
                    ->when($request->type, fn($q) => $q->where('type', $request->type))
                    ->when($request->status, fn($q) => $q->where('status', $request->status))
                    ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
                    ->when($request->due_date, fn($q) => $q->whereDate('due_date', $request->due_date))
                    ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
                    ->with('comments')
                    ->paginate(10);
            });
            return $tasks;
        } catch (Exception $exception) {
            Log::error("Error fetching tasks. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة جلب البيانات');
        }
    }

    public function storeTask(array $Data): Task
    {
        try {
            $task = Task::create([
                'title' => $Data['title'],
                'description' => $Data['description'],
                'type' => $Data['type'],
                'priority' => $Data['priority'],
                'due_date' => $Data['due_date'],
                'assigned_to' => $Data['assigned_to'],
            ]);

            // If there are dependencies, sync them
            if (isset($Data['depends_on'])) {
                $task->dependencies()->sync($Data['depends_on']);
                $task->status = 'blocked';
                $task->save();
            }
            // Clear cached tasks
            Cache::forget('tasks');

            return $task;
        } catch (Exception $exception) {
            Log::error("Error storing task. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تخزين البيانات');
        }
    }

    public function updateTask(Task $task, array $Data): Task
    {
        try {
            // Update the task, filter out any null or empty values using array_filter
            $task->update(array_filter($Data));

            // Check if dependencies are provided, and sync them
            if (isset($Data['depends_on'])) {
                $task->dependencies()->sync($Data['depends_on']);
                $task->status = 'blocked';
                $task->save();
            }

            // Clear cache for tasks
            Cache::forget('tasks');

            return $task;
        } catch (Exception $exception) {
            Log::error("Error updating task. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }

  
    public function updateStatus(Task $task, array $data)
    {
        try {
            // Check if the task has any dependencies that are not completed
            if ($task->dependencies()->where('status', '!=', 'Completed')->exists()) {
                throw new Exception('لا يمكن تغيير حالة المهمة لأن بعض المهام المعتمدة عليها لم تكتمل بعد.');
            }

            // If task has no incomplete dependencies, proceed with the status update
            $task->update(['status' => $data['status']]);

            // If the status is being changed to 'completed'
            if ($data['status'] === 'Completed') {
                // Get all tasks that depend on this task and have status 'blocked'
                $dependentTasks = $task->dependentTasks()->where('status', 'blocked')->get();

                // Update their status to 'open'
                foreach ($dependentTasks as $dependentTask) {
                    $dependentTask->update(['status' => 'open']);
                    // delete the record from the pivot table
                    $task->dependentTasks()->detach($dependentTask->id);
                }
            }
            // Clear cache
            Cache::forget('tasks');
            return $task;

        } catch (Exception $e) {
            Log::error("Error updating task: " . $e->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }



    public function reassignTask(Task $task, $Data): Task
    {
        try {
            $task->update(['assigned_to' => $Data['assigned_to']]);
            cache::forget('tasks');
            return $task;
        } catch (Exception $e) {
            Log::error("Error reassigning task. Error: " . $e->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }

    public function assignTask(Task $task, array $Data): Task
    {
        try {
            $task->update(['assigned_to' => $Data['assigned_to']]);
            cache::forget('tasks');
            return $task;
        } catch (Exception $e) {
            Log::error("Error assigning task. Error: " . $e->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }
}
