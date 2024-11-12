<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class); // Seed roles
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    /** @test */
    public function non_admin_cannot_access_admin_routes()
    {
        $manager = $this->createUserWithRole('manager');

        // Test that a non-admin cannot access the showDeleted route
        $response = $this->actingAs($manager, 'api')->getJson(route('users.deleted'));
        $response->assertStatus(403);

        // Test that a non-admin cannot restore a soft-deleted user
        $userToRestore = User::factory()->create(['deleted_at' => now()]);
        $response = $this->actingAs($manager, 'api')->putJson(route('users.restore', $userToRestore->id));
        $response->assertStatus(403);

        // Test that a non-admin cannot force delete a user
        $userToForceDelete = User::factory()->create(['deleted_at' => now()]);
        $response = $this->actingAs($manager, 'api')->deleteJson(route('users.force-delete', $userToForceDelete->id));
        $response->assertStatus(403);
    }

    /** @test */
    public function user_store_requires_valid_data()
    {
        $admin = $this->createUserWithRole('admin');

        $invalidData = [
            'name' => '', // Name is required
            'email' => 'not-an-email', // Invalid email format
            'password' => 'short', // Too short and missing required characters
            'role' => 'unknown' // Invalid role
        ];

        $response = $this->actingAs($admin, 'api')->postJson(route('users.store'), $invalidData);
        $response->assertStatus(422); // Unprocessable Entity
        $response->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }


    /** @test */
    public function non_admin_cannot_update_user()
    {
        $manager = $this->createUserWithRole('manager');
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Unauthorized Update',
            'email' => 'unauthorized@example.com',
            'password' => 'Unauthorized123!',
            'role' => 'developer'
        ];

        $response = $this->actingAs($manager, 'api')->putJson(route('users.update', $user->id), $updateData);
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $admin = $this->createUserWithRole('admin');
        $user = User::factory()->create();

        $response = $this->actingAs($admin, 'api')->deleteJson(route('users.destroy', $user->id));
        $response->assertStatus(200)->assertJson(['message' => 'User deleted successfully.']);
    }

    /** @test */
    public function non_admin_cannot_delete_user()
    {
        $developer = $this->createUserWithRole('developer');
        $user = User::factory()->create();

        $response = $this->actingAs($developer, 'api')->deleteJson(route('users.destroy', $user->id));
        $response->assertStatus(403);
    }
}
