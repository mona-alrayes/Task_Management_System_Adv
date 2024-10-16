<?php

namespace App\Services\Comment;

use Exception;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class CommentService
{

    public function storeComment(array $data, Task $task): Comment // Assuming Comment is the model for comments
    {
        try {
            // Create and return the new comment associated with the task
            return $task->comments()->create($data);
        } catch (Exception $exception) {
            Log::error("Error storing comment for task ID {$task->id}. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تخزين البيانات');
        }
    }


    public function updateComment(Comment $comment, $Data, string $task_id): Comment
    {
        try {
            $comment->update(array_filter($Data));
            return $comment;
        } catch (Exception $exception) {
            Log::error("Error updating comment. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }
}
