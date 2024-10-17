<?php

namespace App\Services\User;

use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Store a new user and assign a role.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function storeUser(array $data)
    {
        try {
            // Create the user with encrypted password
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' =>$data['password'],
            ]);

            // Find the role by name and assign it to the user
            $role = Role::findByName($data['role']);

            if (!$role) {
                throw ValidationException::withMessages([
                    'role' => ['The selected role is invalid.'],
                ]);
            }

            // Assign the role to the user
            $user->assignRole($role);

            // Generate a JWT token for the user
            $token = auth()->login($user);

            return [
                'User' => $user,
                'Role' => $user->roles->pluck('name'),
                'user-token' => $token,
            ];
        } catch (Exception $exception) {
            Log::error("Error storing user. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تخزين البيانات');
        }
    }

    /**
     * Update an existing user and optionally update their role.
     *
     * @param User $user
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateUser(User $user, array $data)
    {
        try {
            
            // Update the user, filtering out any null or empty values
            $user->update(array_filter($data));

            // If a role is provided, update the user's role
            if (isset($data['role'])) {
                $role = Role::findByName($data['role']);

                if (!$role) {
                    throw ValidationException::withMessages([
                        'role' => ['The selected role is invalid.'],
                    ]);
                }

                // Remove existing roles and assign the new one
                $user->syncRoles([$role]);
            }

            return [
                'User' => $user,
                'Role' => $user->roles->pluck('name'),
            ];
        } catch (Exception $exception) {
            Log::error("Error updating user. Error: " . $exception->getMessage());
            throw new Exception('حدث خطأ أثناء محاولة تحديث البيانات');
        }
    }
}
