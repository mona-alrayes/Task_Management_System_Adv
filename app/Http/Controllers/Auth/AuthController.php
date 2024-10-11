<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

class AuthController extends Controller
{
    protected AuthService $authService;

    /**
     * AuthController constructor.
     *
     * Applies middleware to protect routes except for login and register.
     */
    public function __construct(AuthService $authService)
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
        $this->authService = $authService;
    }

    /**
     * Handle user login.
     *
     * Validates the request and attempts to authenticate the user using
     * the provided email and password. If authentication is successful,
     * returns the user's information along with a JWT token.
     *
     * @param Request $request The HTTP request object containing login credentials.
     * @return \Illuminate\Http\JsonResponse JSON response containing user info and token.
     */
    public function login(LoginRequest $request)
    {
        $AuthData=$this->authService->login($request->validated());

        return response()->json([
            'status' => 'success',
            'user' => $AuthData['user'],
            'authorisation' => [
                'token' => $AuthData['token'],
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Handle user registration.
     *
     * Validates the input data and creates a new user. Automatically logs
     * the user in and returns user information along with a JWT token.
     *
     * @param Request $request The HTTP request object containing registration data.
     * @return \Illuminate\Http\JsonResponse JSON response with created user info and token.
     */
    public function register(RegisterRequest $request)
    {
        
        $user=$this->authService->register($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user['user'],
            'authorisation' => [
                'token' => $user['token'],
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Log the user out.
     *
     * Invalidate the user's token, effectively logging them out.
     *
     * @return \Illuminate\Http\JsonResponse JSON response confirming logout success.
     */
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Refresh the JWT token.
     *
     * Generates a new token for the authenticated user and returns
     * the updated token along with the user's information.
     *
     * @return \Illuminate\Http\JsonResponse JSON response with user info and new token.
     */
    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}