<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


/**
 * Class AuthService
 *
 * Handles operations related to users including login , register , logout , refresh.
 */
class AuthService
{
    //TODO go back here to see how to improve messages and error handling

    public function login(array $data): array
    {
        try {
            // Attempt to authenticate with credentials
            $credentials = [
                'email' => $data['email'],
                'password' => $data['password'],
            ];
    
            $token = Auth::attempt($credentials);

            if (!$token) {
                // Return consistent response structure on failure
                return [
                    'status' => 'خطأ',
                    'message' => 'غير مخول', // Unauthorized
                ];
            }
    
            // Get authenticated user
            $user = Auth::user();
    
            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (Exception $e) {
            // Log any exceptions that may occur
            Log::error(['error' => $e->getMessage()]);
    
            return [
                'status' => 'خطأ',
                'message' => 'حدث خطأ اثناء عملية الدخول.', // Error during login
            ];
        }
    }
    


    /**
     * Register a new user.
     *
     * @param array $data
     * The array containing user registration data including 'name', 'email', 'password', and 'role'.
     *
     * @return array
     * An array containing the user resource, a JWT token, or an error response.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(array $data): array
    {
        try {
            // Manually create the user without the password
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $data['password'];
            $user->save();

            // Generate a JWT token for the user
            $token = Auth::login(user: $user);

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (Exception $e) {
            Log::error(['error' => $e->getMessage()]);
            return [
                'status' => 'خطأ',
                'message' => 'حدث خطأ اثناء عملية إنشاء الحساب',
            ];
        }
    }
}
