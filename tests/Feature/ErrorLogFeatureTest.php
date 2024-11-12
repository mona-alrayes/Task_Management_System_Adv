<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TaskSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ErrorLogFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $developer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(TaskSeeder::class);

        // Create roles and users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->developer = User::factory()->create();
        $this->developer->assignRole('developer');
    }


    public function test_admin_can_only_see_Error_Log_report()
    {
        //step 1: try to visit the route as admin user suppose to return 200 
        $this->actingAs($this->admin, 'api')
            ->getJson(route('Errorlog'))
            ->assertStatus(200);
    }

    public function test_none_admin_can_not_see_Error_Log_report()
    {
       
        //try to visit the route as developer or manager user to not allow it since they are not athorized returning 403 status code
        $this->actingAs($this->developer, 'api')
            ->getJson(route('Errorlog'))
            ->assertStatus(403);
        $this->actingAs($this->manager, 'api')
            ->getJson(route('Errorlog'))
            ->assertStatus(403);
    }
}
