<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\User\UserService;
use App\Http\Requests\User\storeUserRequest;
use App\Http\Requests\User\updateUserRequest;

class UserController extends Controller
{
    protected UserService $userService;

    /**
     * Constructor
     * 
     * Injecting the UserService via constructor to follow the Dependency Injection (DI) principle.
     * This decouples the business logic from the controller, making it more modular and testable.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     * 
     * Using pagination to prevent returning large datasets in one response.
     * This improves performance and is a common best practice when handling large collections.
     * 
     * @return JsonResponse
     */
    public function index()
    {
        $users = User::paginate(10);
        return self::paginated($users, 'Users retrieved successfully.', 200);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * Using FormRequest for validation ensures separation of concerns.
     * Only the validated data is passed to the service layer, promoting clean and maintainable code.
     *
     * @param storeUserRequest $request
     * @return JsonResponse
     */
    public function store(storeUserRequest $request)
    {
        $user = $this->userService->storeUser($request->validated());
        return self::success($user, 'User created successfully');
    }

    /**
     * Display the specified resource.
     * 
     * Type-hinting User model allows for route model binding, which simplifies fetching the user instance.
     * This is a best practice to reduce manual model retrieval.
     * 
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user)
    {
        return self::success($user, 'User retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     * 
     * Again, using FormRequest for validation ensures only valid data is passed to the update logic.
     * Route model binding automatically fetches the user, making the code cleaner.
     *
     * @param updateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(updateUserRequest $request, User $user)
    {
        $updatedUser = $this->userService->updateUser($user, $request->validated());
        return self::success($updatedUser, 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     * 
     * Soft deleting the user allows for future restoration.
     * If data should not be permanently deleted immediately, this is the best practice to avoid data loss.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user)
    {
        $user->delete();
        return self::success(null, 'User deleted successfully.');
    }

    /**
     * Display soft-deleted users.
     * 
     * Best practice: Separating active and soft-deleted records is useful for showing or restoring previously deleted records.
     * Using `onlyTrashed()` helps to clearly retrieve only soft-deleted users.
     *
     * @return JsonResponse
     */
    public function showDeleted()
    {
        $softDeleted = User::onlyTrashed()->get();
        if ($softDeleted->isEmpty()) {
            return self::error(null, 'No deleted users found.', 404);
        }
        return self::success($softDeleted, 'Soft-deleted users retrieved successfully.');
    }

    /**
     * Restore a soft-deleted user.
     * 
     * Restoring a soft-deleted user is an important feature in cases where a user may be deleted by mistake.
     * Use `findOrFail()` to ensure proper error handling in case the user is not found.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function restoreDeleted(string $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return self::success($user, 'User restored successfully.');
    }

    /**
     * Permanently delete a soft-deleted user.
     * 
     * For data compliance or complete removal, you can implement this method to handle permanent deletion.
     * Use `forceDelete()` carefully, as it will remove the user data permanently from the database.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function forceDeleted(string $id)
    {
        User::onlyTrashed()->findOrFail($id)->forceDelete();
        return self::success(null, 'User permanently deleted.');
    }
}
