<?php

namespace App\Services\Task;

use Exception;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class TaskService
{
    public function getTasks($request): LengthAwarePaginator
    {
        try {
            $tasks=Cache::remember('tasks', 3600, function ($request) {
                Task::query()
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

    public function storeTask($Data): Task
    {
        try {
            $task = Task::create($Data);
            cache::forget('task');
            return $task;
        } catch (Exception $exception) {
            Log::error("Error storing task. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تخزين البيانات');
        }
    }

    public function updateTask(Task $task, $Data): Task
    {
        try {
            $task->update(array_filter($Data));
            cache::forget('task');
            return $task;
        } catch (ModelNotFoundException $e) {
            Log::error("Task not found. Error: " . $e->getMessage());
            throw new Exception('الموديل غير موجودة');
        } catch (RelationNotFoundException $e) {
            Log::error("Relation not found. Error: " . $e->getMessage());
            throw new Exception('خطأ في عملية التحقق من الرابط');
        } catch (Exception $exception) {
            Log::error("Error updating task. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }
    #TODO go back here for error handling
    public function updateStatus(Task $task, array $data)
    {
        try {
            $task->update(['status' => $data['status']]);
            cache::forget('task');
            return response()->json([
                'message' => 'Task status updated successfully.',
                'task' => $task
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Return clean JSON response for task not found
            return response()->json([
                'message' => 'لم يتم العثور على المهمة.',
            ], 404);
        } catch (Exception $e) {
            // Log and handle other exceptions
            Log::error("Error updating task: " . $e->getMessage());

            return response()->json([
                'message' => 'حدث خطأ أثناء محاولة تحديث المهمة.',
            ], 500);
        }
    }


    public function reassignTask(Task $task, $Data): Task
    {
        try {
            $task->update(['assigned_to' => $Data['assigned_to']]);
            cache::forget('task');
            return $task;
        } catch (ModelNotFoundException $e) {
            Log::error("Task not found for reassignment. Error: " . $e->getMessage());
            throw new Exception('الموديل غير موجودة');
        } catch (Exception $e) {
            Log::error("Error reassigning task. Error: " . $e->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }

    public function assignTask(Task $task, array $Data): Task
    {
        try {
            $task->update(['assigned_to' => $Data['assigned_to']]);
            cache::forget('task');
            return $task;
        } catch (ModelNotFoundException $e) {
            Log::error("Task not found for assignment. Error: " . $e->getMessage());
            throw new Exception('الموديل غير موجودة');
        } catch (Exception $e) {
            Log::error("Error assigning task. Error: " . $e->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }
}
