<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Comment;
use Illuminate\Http\Request;
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
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request)
    {
        // Implementation will go here
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
     * @param string $task_id
     * @return JsonResponse
     */
    public function show(Comment $comment, string $task_id): JsonResponse
    {
        return self::success($comment, 'Comment retrieved successfully');
    }

    /**
     * Update the specified comment in storage.
     *
     * @param UpdateCommentRequest $request
     * @param Comment $comment
     * @param string $task_id
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(UpdateCommentRequest $request, Comment $comment, string $task_id): JsonResponse
    {
        $updatedComment = $this->commentService->updateComment($comment, $request->validated(), $task_id);
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
