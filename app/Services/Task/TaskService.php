<?php

namespace App\Services\Task;

use Exception;
use App\Models\Task;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;


class TaskService
{

    /**
     * get all Tasks with comments they have
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getTasks($request): LengthAwarePaginator
    {
        try {
            // return all tasks with all types of filters on the data retrived
           $tasks= Task::all()
           ->when($request->type , fn($q)=> $q->type($request->type))
           ->when($request->status , fn($q)=> $q->status($request->status))
           ->when($request->assigned_to, fn($q)=> $q->assigned_to($request->assigned_))
           ->when($request->due_date , fn($q)=> $q->due_date($request->due_date))
           ->when($request->priority , fn($q)=> $q->priority($request->priority))
           ->paginate();
           return $tasks;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة جلب البيانات');
        } catch (ModelNotFoundException $e){
            Log::error($e->getMessage());
            throw new Exception('الموديل غير موجودة');
        } catch (RelationNotFoundException $e){
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
            return $task->load('comments');
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
            return $task->load('comments');
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }
}
