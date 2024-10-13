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

    protected CommentService $CommentService;

    public function __construct(CommentService $CommentService)
    {
        $this->CommentService = $CommentService;
    }

    /**
     * Display a listing of the resource.
     * @throws \Exception
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */

    public function store(StoreCommentRequest $request, Task $task): JsonResponse
    {
        $comment = $this->CommentService->storeComment($request->validated(), $task);
        return self::success($comment, 'Comment created successfully', 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Comment $comment, string $task_id): JsonResponse
    {
        return self::success($comment, 'Comment retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     * @throws \Exception
     */
    public function update(UpdateCommentRequest $request, Comment $comment, string $task_id): JsonResponse
    {
        $updatedComment = $this->CommentService->updateComment($comment, $request->validated(), $task_id);
        return self::success($updatedComment, 'Comment updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();
        return self::success(null, 'Comment deleted successfully');
    }

    /**
     * Display soft-deleted records.
     */
    public function showDeleted(): JsonResponse
    {
        $comment = Comment::onlyTrashed()->get();
        return self::success($comment, 'Comments retrieved successfully');
    }

    /**
     * Restore a soft-deleted record.
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
     * Permanently delete a soft-deleted record.
     * @param string $id
     * @return JsonResponse
     */
    public function forceDeleted(string $id): JsonResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($id)->forceDelete();
        return self::success(null, 'Comment force deleted successfully');
    }
}
