<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function index(Request $request): JsonResponse
    {
        ${{ modelVariablePlural }} = $this->CommentService->getComments($request);
        return self::paginated(${{ modelVariablePlural }}, 'Comments retrieved successfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $comment = $this->CommentService->storeComment($request->validated());
        return self::success($comment, 'Comment created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment): JsonResponse
    {
        return self::success($comment, 'Comment retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     * @throws \Exception
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $updatedComment = $this->CommentService->updateComment($comment, $request->validated());
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
        ${{ modelVariablePlural }} = Comment::onlyTrashed()->get();
        return self::success(${{ modelVariablePlural }}, 'Comments retrieved successfully');
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