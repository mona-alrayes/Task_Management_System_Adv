<?php

namespace App\Services\Task;

use Exception;
use App\Models\Task;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpParser\Node\Stmt\TryCatch;

class TaskService
{

    public function getTasks($request): LengthAwarePaginator
{
    try {
        // Use Task::query() to build a query with filters applied
        $tasks = Task::query()
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to)) // Fixed typo here
            ->when($request->due_date, fn($q) => $q->whereDate('due_date', $request->due_date))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->with('comments') // Eager load comments to avoid N+1 issue
            ->paginate(10); // You can set a default value for pagination, like 10 tasks per page
        
        return $tasks;

    } catch (Exception $exception) {
        Log::error($exception->getMessage());
        throw new Exception('حدث خطأ أثناء محاولة جلب البيانات');
    } catch (ModelNotFoundException $e) {
        Log::error($e->getMessage());
        throw new Exception('الموديل غير موجودة');
    } catch (RelationNotFoundException $e) {
        Log::error($e->getMessage());
        throw new Exception('خطأ في عملية التحقق من الرابط');
    }
}



    /**
     * store new Task in storage
     *
     * @param [type] $Data
     * @return Task
     * @throws Exception
     */
    public function storeTask($Data): Task
    {
        try {
            $task = Task::create($Data);
            return $task;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تخزين البيانات');
        }
    }


    /**
     * update specific Task
     * @throws Exception
     * @param Task $task
     * @param [type] $Data
     * @return Task
     */
    public function updateTask(Task $task, $Data): Task
    {
        try {
            $task->update(array_filter($Data));
            return $task;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }catch (ModelNotFoundException $e){
            Log::error($e->getMessage());
            throw new Exception('الموديل غير موجودة');
        } catch (RelationNotFoundException $e){
            Log::error($e->getMessage());
            throw new Exception('خطأ في عملية التحقق من الرابط');
        }
    }
    /**
     * reassigned Task to a User
     *
     * @param  string  $id Task
     * @param  [type]  $Data
     * @return Task
     */
    public function reassignTask(string $id , $Data):Task
    {
        try {
            $task = Task::findOrFail($id);
            $task->update(['assigned_to' => $Data['assigned_to']]);
            return $task;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }catch (ModelNotFoundException $e){
            Log::error($e->getMessage());
            throw new Exception('الموديل غير موجودة');
        } catch (RelationNotFoundException $e){
            Log::error($e->getMessage());
            throw new Exception('خطأ في عملية التحقق من الرابط');
        }
    }

    /**
     * assign Task to a User
     *
     * @param  Task  $task
     * @param  [type]  $Data
     * @return Task
     */
    public function assignTask(string $id, array $Data): Task
{
    try {
        $task = Task::findOrFail($id);
        $task->update(['assigned_to' => $Data['assigned_to']]);
        return $task;
    } catch (ModelNotFoundException $e) {
        Log::error("Task not found: " . $e->getMessage());
        throw new Exception('الموديل غير موجودة');
    } catch (Exception $e) {
        Log::error('Update error: ' . $e->getMessage());
        throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
    }catch (RelationNotFoundException $e){
        Log::error($e->getMessage());
        throw new Exception('خطأ في عملية التحقق من الرابط');
    }
}

}
