<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Task\TaskService;
use Illuminate\Support\Facades\Cache;
use App\Services\Assets\AssetsService;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\assignedToRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateStatusRequest;

class TaskController extends Controller
{
    protected TaskService $taskService;
    protected AssetsService $assetsService;

    public function __construct(TaskService $taskService, AssetsService $assetsService)
    {
        $this->taskService = $taskService;
        $this->assetsService = $assetsService;
    }

    /**
     * Display a listing of tasks.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tasks = $this->taskService->getTasks($request);
            return self::paginated($tasks, 'Tasks retrieved successfully.', 200);
        } catch (\Exception $e) {
            return self::error(null, 'Failed to retrieve tasks.', 500);
        }
    }

    /**
     * Store a newly created task.
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $task = $this->taskService->storeTask($request->validated());
            return self::success($task, 'Task created successfully.', 201);
        } catch (\Exception $e) {
            return self::error(null, 'Failed to create task.', 500);
        }
    }

    /**
     * Upload an attachment for a specific task.
     *
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function uploadAttachment(Request $request, Task $task): JsonResponse
    {
        $request->validate(['file' => 'required|file|max:2048']);

        try {
            $file = $request->file('file');
            $attachment = $this->assetsService->storeAttachment($file, Task::class, $task->id);
            return self::success($attachment, 'File uploaded successfully.', 201);
        } catch (\Exception $e) {
            return self::error(null, 'Failed to upload file.', 500);
        }
    }

    /**
     * Display a specified task.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        try {
            $taskData = Cache::remember('task_' . $task->id, 3600, fn() => $task->load('comments'));
            return self::success($taskData, 'Task retrieved successfully.');
        } catch (\Exception $e) {
            return self::error(null, 'Failed to retrieve task.', 500);
        }
    }

    /**
     * Update a specified task.
     *
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $updatedTask = $this->taskService->updateTask($task, $request->validated());
            return self::success($updatedTask, 'Task updated successfully.');
        } catch (\Exception $e) {
            return self::error(null, 'Failed to update task.', 500);
        }
    }

    /**
     * Change the status of a specified task.
     *
     * @param UpdateStatusRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function statusChange(UpdateStatusRequest $request, Task $task): JsonResponse
    {
        try {
            $updateStatusTask = $this->taskService->updateStatus($task, $request->validated());
            return self::success($updateStatusTask, 'Task status updated successfully.');
        } catch (\Exception $e) {
            return self::error(null, $e->getMessage(), 400); // Better handling of specific error messages
        }
    }

    /**
     * Reassign a task to another user.
     *
     * @param assignedToRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function reassignTask(assignedToRequest $request, Task $task): JsonResponse
    {
        try {
            $reassignedTask = $this->taskService->reassignTask($task, $request->validated());
            return self::success($reassignedTask, 'Task reassigned successfully.');
        } catch (\Exception $e) {
            return self::error(null, 'Failed to reassign task.', 500);
        }
    }

    /**
     * Assign a task to a user.
     *
     * @param AssignedToRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function assignTask(AssignedToRequest $request, Task $task): JsonResponse
    {
        try {
            $assignedTask = $this->taskService->assignTask($task, $request->validated());
            return self::success($assignedTask, 'Task assigned successfully.');
        } catch (\Exception $e) {
            return self::error(null, 'Failed to assign task.', 500);
        }
    }

    /**
     * Display tasks that are in "blocked" status.
     *
     * @return JsonResponse
     */
    public function blockedTasks(): JsonResponse
    {
        try {
            $blockedTasks = Task::blockedTasks();
            return self::success($blockedTasks, 'Blocked tasks retrieved successfully.');
        } catch (\Exception $e) {
            return self::error(null, 'Failed to retrieve blocked tasks.', 500);
        }
    }

    /**
     * Remove a specified task.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        try {
            $task->delete();
            Cache::forget('task_' . $task->id);
            $this->taskService->clearTaskCache();
            return self::success(null, 'Task deleted successfully.');
        } catch (\Exception $e) {
            return self::error(null, 'Failed to delete task.', 500);
        }
    }

    /**
     * Display soft-deleted tasks.
     *
     * @return JsonResponse
     */
    public function showDeleted(): JsonResponse
    {
        try {
            $softDeleted = Task::onlyTrashed()->get();
            if ($softDeleted->isEmpty()) {
                return self::error(null, 'No deleted tasks found.', 404);
            }
            return self::success($softDeleted, 'Soft-deleted tasks retrieved successfully.');
        } catch (\Exception $e) {
            return self::error(null, 'Failed to retrieve deleted tasks.', 500);
        }
    }

    /**
     * Restore a soft-deleted task.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function restoreDeleted(string $id): JsonResponse
    {
        try {
            $task = Task::onlyTrashed()->findOrFail($id);
            $task->restore();
            Cache::forget('task_' . $task->id);
            $this->taskService->clearTaskCache();
            return self::success($task, 'Task restored successfully.');
        } catch (\Exception $e) {
            return self::error(null, 'Failed to restore task.', 500);
        }
    }

    /**
     * Permanently delete a soft-deleted task.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function forceDeleted(string $id): JsonResponse
    {
        try {
            Task::onlyTrashed()->findOrFail($id)->forceDelete();
            Cache::forget('task_' . $id);
            $this->taskService->clearTaskCache();
            return self::success(null, 'Task permanently deleted.');
        } catch (\Exception $e) {
            return self::error(null, 'Failed to permanently delete task.', 500);
        }
    }
}
