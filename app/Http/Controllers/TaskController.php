<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Task\TaskService;
use Illuminate\Support\Facades\Cache;
use App\Services\Assets\AssetsService;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\AssignedToRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateStatusRequest;

class TaskController extends Controller
{
    protected TaskService $taskService;
    protected AssetsService $assetsService; // Assuming AssetsService exists and implements the storeAttachment method

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
     * @throws \Exception
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getTasks($request);
        return self::paginated($tasks, 'Tasks retrieved successfully.', 200);
    }

    /**
     * Store a newly created task.
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->storeTask($request->validated());
        return self::success($task, 'Task created successfully.', 201);
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
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);
        $file = $request->file('file');
        $attachment = $this->assetsService->storeAttachment($file, Task::class, $task->id);
        return self::success($attachment, 'File uploaded successfully.', 201);
    }

    /**
     * Display a specified task.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        $taskData = Cache::remember('task_' . $task->id, 3600, function () use ($task) {
            return $task;
        });
        return self::success($taskData->load('comments'), 'Task retrieved successfully.');
    }

    /**
     * Update a specified task.
     *
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updatedTask = $this->taskService->updateTask($task, $request->validated());
        return self::success($updatedTask, 'Task updated successfully.');
    }

    /**
     * Change the status of a specified task.
     *
     * @param UpdateStatusRequest $request
     * @param Task $task
     * @return JsonResponse
     * @throws \Exception
     */
    public function statusChange(UpdateStatusRequest $request, Task $task): JsonResponse
    {
        $updateStatusTask = $this->taskService->updateStatus($task, $request->validated());
        return self::success($updateStatusTask, 'Task status updated successfully.');
    }

    /**
     * Reassign a task to another user.
     *
     * @param AssignedToRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function reassignTask(AssignedToRequest $request, Task $task): JsonResponse
    {
        $reassignedTask = $this->taskService->reassignTask($task, $request->validated());
        return self::success($reassignedTask, 'Task reassigned successfully.');
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
        $assignedTask = $this->taskService->assignTask($task, $request->validated());
        return self::success($assignedTask, 'Task assigned to user successfully.');
    }

    /**
     * Display tasks that are in "blocked" status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function blockedTasks(Request $request): JsonResponse
    {
        $blockedTasks = Task::blockedTasks();
        return self::success($blockedTasks, 'Blocked tasks retrieved successfully.');
    }

    /**
     * Remove a specified task.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        Cache::forget('tasks');
        Cache::forget('task_' . $task->id);
        return self::success(null, 'Task deleted successfully.');
    }

    /**
     * Display soft-deleted tasks.
     *
     * @return JsonResponse
     */
    public function showDeleted(): JsonResponse
    {
        $softDeleted = Task::onlyTrashed()->get();
        if ($softDeleted->isEmpty()) {
            return self::error(null, 'No deleted tasks found.', 404);
        }
        return self::success($softDeleted, 'Soft-deleted tasks retrieved successfully.');
    }

    /**
     * Restore a soft-deleted task.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function restoreDeleted(string $id): JsonResponse
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $task->restore();
        Cache::forget('tasks');
        Cache::forget('task_' . $task->id);
        return self::success($task, 'Task restored successfully.');
    }

    /**
     * Permanently delete a soft-deleted task.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function forceDeleted(string $id): JsonResponse
    {
        Task::onlyTrashed()->findOrFail($id)->forceDelete();
        Cache::forget('tasks');
        Cache::forget('task_' . $id);
        return self::success(null, 'Task permanently deleted.');
    }
}
