<?php

namespace App\Services\Task;

use Exception;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    // Helper function to generate cache key for tasks
    private function generateCacheKey($request)
    {
        return 'tasks_' . md5(serialize($request->all()));
    }

    // Method to clear task cache manually
    public function clearTaskCache()
    {
        // Get the cache keys list
        $cacheKeys = Cache::get('task_cache_keys', []);

        // Loop through and forget each cached task entry
        foreach ($cacheKeys as $cacheKey) {
            Cache::forget($cacheKey);
        }

        // Optionally clear the cache key list itself
        Cache::forget('task_cache_keys');
    }

    // Method to get tasks, caching logic included
    public function getTasks($request): LengthAwarePaginator
    {
        try {
            $cacheKey = $this->generateCacheKey($request);

            // Store cache keys for later clearing
            $cacheKeys = Cache::get('task_cache_keys', []);
            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('task_cache_keys', $cacheKeys);
            }

            // Cache the tasks
            $tasks = Cache::remember($cacheKey, 3600, function () use ($request) {
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

    // Store a new task
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

            if (isset($Data['depends_on'])) {
                $task->dependencies()->sync($Data['depends_on']);
                $task->status = 'blocked';
                $task->save();
            }

            // Clear task cache after storing new task
            $this->clearTaskCache();

            return $task;
        } catch (Exception $exception) {
            Log::error("Error storing task. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تخزين البيانات');
        }
    }

    // Update an existing task
    public function updateTask(Task $task, array $Data): Task
    {
        try {
            $task->update(array_filter($Data));

            if (isset($Data['depends_on'])) {
                $task->dependencies()->sync($Data['depends_on']);
                $task->status = 'blocked';
                $task->save();
            }

            // Clear task cache after updating task
            $this->clearTaskCache();

            return $task;
        } catch (Exception $exception) {
            Log::error("Error updating task. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }

    // Update task status with dependency checks
    public function updateStatus(Task $task, array $data)
    {
        try {
            if ($task->dependencies()->where('status', '!=', 'Completed')->exists()) {
                throw new Exception('لا يمكن تغيير حالة المهمة لأن بعض المهام المعتمدة عليها لم تكتمل بعد.');
            }

            $task->update(['status' => $data['status']]);

            if ($data['status'] === 'Completed') {
                $dependentTasks = $task->dependentTasks()->where('status', 'blocked')->get();

                foreach ($dependentTasks as $dependentTask) {
                    $dependentTask->update(['status' => 'open']);
                    $task->dependentTasks()->detach($dependentTask->id);
                }
            }

            // Clear task cache after updating task status
            $this->clearTaskCache();

            return $task;
        } catch (Exception $e) {
            Log::error("Error updating task status: " . $e->getMessage());
            throw new Exception($e->getMessage()); 
        }
    }

    // Reassign a task
    public function reassignTask(Task $task, $Data): Task
    {
        try {
            $task->update(['assigned_to' => $Data['assigned_to']]);

            // Clear task cache after reassigning task
            $this->clearTaskCache();

            return $task;
        } catch (Exception $e) {
            Log::error("Error reassigning task. Error: " . $e->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }

    // Assign task to a user
    public function assignTask(Task $task, array $Data): Task
    {
        try {
            $task->update(['assigned_to' => $Data['assigned_to']]);

            // Clear task cache after assigning task
            $this->clearTaskCache();

            return $task;
        } catch (Exception $e) {
            Log::error("Error assigning task. Error: " . $e->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }
}
