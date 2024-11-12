<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TaskSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportFeatureTest extends TestCase
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



    public function update_tasks()
    {
        $task1 = Task::find(1);
        $task1->update([
            'status' => 'In-progress',
        ]);
        $task2 = Task::find(2);
        $task2->update([
            'status' => 'Completed',
        ]);
    }
    public function test_admin_can_only_see_daily_Tasks_report()
    {
        //step 1 : update some tasks 
        $this->update_tasks();
        //try to visit the route as admin user suppose to return 200 
        $this->actingAs($this->admin, 'api')
            ->getJson(route('dailyTasks'))
            ->assertStatus(200);
    }

    public function test_none_admin_can_not_see_daily_report()
    {
        //step 1 : update some tasks 
        $this->update_tasks();
        //try to visit the route as developer or manager user to not allow it since they are not athorized returning 403 status code
        $this->actingAs($this->developer, 'api')
            ->getJson(route('dailyTasks'))
            ->assertStatus(403);
        $this->actingAs($this->manager, 'api')
            ->getJson(route('dailyTasks'))
            ->assertStatus(403);
    }
}
