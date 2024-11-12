<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use App\Services\Comment\CommentService;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Display a listing of the comments.
     *
     * @param Task $task 
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Task $task)
    {
        $comments = $task->comments()->paginate(10);
        return Self::paginated($comments , 'Tasks retrieved successfully.', 200);
    }

    /**
     * Store a newly created comment in storage.
     *
     * @param StoreCommentRequest $request
     * @param Task $task
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(StoreCommentRequest $request, Task $task): JsonResponse
    {
        $comment = $this->commentService->storeComment($request->validated(), $task);
        return self::success($comment, 'Comment created successfully', 201);
    }

    /**
     * Display the specified comment.
     *
     * @param Comment $comment
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task, Comment $comment): JsonResponse
    {
        $taskComment = $task->comments()->findOrFail($comment->id);
        return self::success($taskComment, 'Comment retrieved successfully');
    }

    /**
     * Update the specified comment in storage.
     *
     * @param UpdateCommentRequest $request
     * @param Comment $comment
     * @param Task $task
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(UpdateCommentRequest $request, Task $task, Comment $comment): JsonResponse
    {
        $updatedComment = $this->commentService->updateComment($comment, $request->validated(), $task);
        return self::success($updatedComment, 'Comment updated successfully');
    }

    /**
     * Remove the specified comment from storage.
     *
     * @param Comment $comment
     * @return JsonResponse
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();
        return self::success(null, 'Comment deleted successfully');
    }

    /**
     * Display soft-deleted comments.
     *
     * @return JsonResponse
     */
    public function showDeleted(): JsonResponse
    {
        $comments = Comment::onlyTrashed()->get();
        return self::success($comments, 'Comments retrieved successfully');
    }

    /**
     * Restore a soft-deleted comment.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function restoreDeleted(string $id): JsonResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);
        $comment->restore();
        return self::success($comment, 'Comment restored successfully');
    }

    /**
     * Permanently delete a soft-deleted comment.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function forceDeleted(string $id): JsonResponse
    {
        Comment::onlyTrashed()->findOrFail($id)->forceDelete();
        return self::success(null, 'Comment force deleted successfully');
    }
}
