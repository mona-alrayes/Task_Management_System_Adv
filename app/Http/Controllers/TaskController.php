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

    protected TaskService $TaskService;
    protected AssetsService $assetsService; // Assuming AssetsService exists and implements the storeAttachment method

    public function __construct(TaskService $TaskService , AssetsService $assetsService)
    {
        $this->TaskService = $TaskService;
        $this->assetsService = $assetsService;
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
    * store files in the disk and database
    *
    * @param  Request  $request
    * @param  Task  $task
    * @return void
    */
    public function uploadAttachment(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file|max:2048', 
        ]);
        $file = $request->file('file');
        $attachment = $this->assetsService->storeAttachment($file, Task::class, $task->id);
        return self::success($attachment, 'File uploaded successfully' , 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): JsonResponse
    {
        $taskData = Cache::remember('task_' . $task->id, 3600, function () use ($task) {
            return $task;
        });
        return self::success($taskData->load('comments'), 'Task retrieved successfully');
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
    #TODO go back here for error handling
    /**
     * Update the status of the specified resource in storage.
     * @throws \Exception
     */
    public function statusChange(UpdateStatusRequest $request, Task $task)
    {
        // Call the service to update status and return its response
        return $this->TaskService->updateStatus($task, $request->validated());
    }

    /**
     * reassignTask PUT Method
     *
     * @param  Request  $request
     * @param  Task  $task
     * @return void
     */
    public function reassignTask(assignedToRequest $request, Task $task)
    {
        $reassignedTask = $this->TaskService->reassignTask($task, $request->validated());
        return self::success($reassignedTask, 'Task reassigned successfully');
    }
    /**
     * assignTask Post Method
     *
     * @param  assignedToRequest  $request
     * @param  Task $task
     * @return void
     */
    public function assignTask(assignedToRequest $request, Task $task)
    {
        $assignedTask = $this->TaskService->assignTask($task, $request->validated());
        return self::success($assignedTask, 'Task been assgined To User Sucessfully');
    }

    /**
     * show tasks that has status = blocked
     *
     * @param  Request  $request
     * @return void
     */

    public function blockedTasks(Request $request)
    {
        $blockedTasks = Task::blockedTasks();
        return self::success($blockedTasks, 'Blocked tasks retrieved successfully');
    }

    public function addAttachment(Request $request, Task $task) {}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        Cache::forget('tasks');
        Cache::forget('task_' . $task->id);
        return self::success(null, 'Task deleted successfully');
    }

    /**
     * Display soft-deleted records.
     */
    public function showDeleted(): JsonResponse
    {
        $softdeleted = Task::onlyTrashed()->get();
        if (!$softdeleted) {
            return self::error(null, 'no soft-deleted tasks found', 404);
        }
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
        Cache::forget('tasks');
        Cache::forget('task_' . $task->id);
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
        Cache::forget('tasks');
        Cache::forget('task_' . $task->id);
        return self::success(null, 'Task force deleted successfully');
    }
}
