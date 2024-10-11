<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Task\TaskService;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;

class TaskController extends Controller
{

    protected TaskService $TaskService;

    public function __construct(TaskService $TaskService)
    {
        $this->TaskService = $TaskService;
    }

    /**
     * Display a listing of the resource.
     * @throws \Exception
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->TaskService->getTasks($request);
        return self::paginated($tasks, 'Tasks retrieved successfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->TaskService->storeTask($request->validated());
        return self::success($task, 'Task created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): JsonResponse
    {
        return self::success($task, 'Task retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     * @throws \Exception
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updatedTask = $this->TaskService->updateTask($task, $request->validated());
        return self::success($updatedTask, 'Task updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        return self::success(null, 'Task deleted successfully');
    }

    /**
     * Display soft-deleted records.
     */
    public function showDeleted(): JsonResponse
    {
        $softdeleted = Task::onlyTrashed()->get();
        return self::success($softdeleted, 'Deleted Tasks retrieved successfully');
    }

    /**
     * Restore a soft-deleted record.
     * @param string $id
     * @return JsonResponse
     */
    public function restoreDeleted(string $id): JsonResponse
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $task->restore();
        return self::success($task, 'Task restored successfully');
    }

    /**
     * Permanently delete a soft-deleted record.
     * @param string $id
     * @return JsonResponse
     */
    public function forceDeleted(string $id): JsonResponse
    {
        $task = Task::onlyTrashed()->findOrFail($id)->forceDelete();
        return self::success(null, 'Task force deleted successfully');
    }
}