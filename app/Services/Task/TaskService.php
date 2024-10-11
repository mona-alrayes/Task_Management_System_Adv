<?php

namespace App\Services\Task;

use Exception;
use App\Models\Task;
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
           $tasks= Task::all()->with('comments')->paginate();
           return $tasks;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
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
            throw new Exception($exception->getMessage());
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
            throw new Exception($exception->getMessage());
        }
    }
}
