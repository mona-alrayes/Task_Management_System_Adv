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

    public function __construct(TaskService $TaskService, AssetsService $assetsService)
    {
        $this->TaskService = $TaskService;
        $this->assetsService = $assetsService;
    }

    /**
     * عرض قائمة المهام.
     * @throws \Exception
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->TaskService->getTasks($request);
        return self::paginated($tasks, 'تم استرجاع المهام بنجاح', 200);
    }

    /**
     * تخزين مهمة جديدة.
     * @throws \Exception
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->TaskService->storeTask($request->validated());
        return self::success($task, 'تم إنشاء المهمة بنجاح', 201);
    }

    /**
     * تخزين الملفات في القرص وقاعدة البيانات.
     *
     * @param  Request  $request
     * @param  Task  $task
     * @return JsonResponse
     */
    public function uploadAttachment(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);
        $file = $request->file('file');
        $attachment = $this->assetsService->storeAttachment($file, Task::class, $task->id);
        return self::success($attachment, 'تم رفع الملف بنجاح', 201);
    }

    /**
     * عرض مهمة محددة.
     */
    public function show(Task $task): JsonResponse
    {
        $taskData = Cache::remember('task_' . $task->id, 3600, function () use ($task) {
            return $task;
        });
        return self::success($taskData->load('comments'), 'تم استرجاع المهمة بنجاح');
    }

    /**
     * تحديث مهمة محددة.
     * @throws \Exception
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updatedTask = $this->TaskService->updateTask($task, $request->validated());
        return self::success($updatedTask, 'تم تحديث المهمة بنجاح');
    }

    /**
     * تغيير حالة مهمة محددة.
     * @throws \Exception
     */
    public function statusChange(UpdateStatusRequest $request, Task $task): JsonResponse
    {
        $updateStatusTask = $this->TaskService->updateStatus($task, $request->validated());
        return self::success($updateStatusTask, 'تم تحديث حالة المهمة بنجاح');
    }

    /**
     * إعادة تعيين مهمة لمستخدم آخر.
     *
     * @param  assignedToRequest  $request
     * @param  Task  $task
     * @return JsonResponse
     */
    public function reassignTask(assignedToRequest $request, Task $task): JsonResponse
    {
        $reassignedTask = $this->TaskService->reassignTask($task, $request->validated());
        return self::success($reassignedTask, 'تم إعادة تعيين المهمة بنجاح');
    }

    /**
     * تعيين مهمة لمستخدم.
     *
     * @param  assignedToRequest  $request
     * @param  Task  $task
     * @return JsonResponse
     */
    public function assignTask(assignedToRequest $request, Task $task): JsonResponse
    {
        $assignedTask = $this->TaskService->assignTask($task, $request->validated());
        return self::success($assignedTask, 'تم تعيين المهمة للمستخدم بنجاح');
    }

    /**
     * عرض المهام التي حالتها "محظورة".
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function blockedTasks(Request $request): JsonResponse
    {
        $blockedTasks = Task::blockedTasks();
        return self::success($blockedTasks, 'تم استرجاع المهام المحظورة بنجاح');
    }

    /**
     * إزالة مهمة محددة.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        Cache::forget('tasks');
        Cache::forget('task_' . $task->id);
        return self::success(null, 'تم حذف المهمة بنجاح');
    }

    /**
     * عرض المهام المحذوفة (soft-deleted).
     */
    public function showDeleted(): JsonResponse
    {
        $softdeleted = Task::onlyTrashed()->get();
        if ($softdeleted->isEmpty()) {
            return self::error(null, 'لم يتم العثور على مهام محذوفة', 404);
        }
        return self::success($softdeleted, 'تم استرجاع المهام المحذوفة بنجاح');
    }

    /**
     * استعادة مهمة محذوفة.
     * @param string $id
     * @return JsonResponse
     */
    public function restoreDeleted(string $id): JsonResponse
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $task->restore();
        Cache::forget('tasks');
        Cache::forget('task_' . $task->id);
        return self::success($task, 'تم استعادة المهمة بنجاح');
    }

    /**
     * حذف مهمة محذوفة بشكل دائم.
     * @param string $id
     * @return JsonResponse
     */
    public function forceDeleted(string $id): JsonResponse
    {
        Task::onlyTrashed()->findOrFail($id)->forceDelete();
        Cache::forget('tasks');
        Cache::forget('task_' . $id);
        return self::success(null, 'تم حذف المهمة بشكل دائم');
    }
}
