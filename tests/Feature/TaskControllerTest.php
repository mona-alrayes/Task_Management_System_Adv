<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $developer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        // Create roles and users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->developer = User::factory()->create();
        $this->developer->assignRole('developer');
    }

    // /** @test */
    // public function an_admin_can_show_deleted_tasks()
    // {
    //     $this->actingAs($this->admin, 'api')
    //         ->getJson(route('tasks.deleted'))
    //         ->assertStatus(200);
    // }

    // /** @test */
    // public function an_admin_can_restore_a_deleted_task()
    // {
    //     $task = Task::factory()->trashed()->create();

    //     $this->actingAs($this->admin, 'api')
    //         ->putJson(route('tasks.restore', ['id' => $task->id]))
    //         ->assertStatus(200);
    // }

    // /** @test */
    // public function an_admin_can_permanently_delete_a_task()
    // {
    //     $task = Task::factory()->trashed()->create();

    //     $this->actingAs($this->admin, 'api')
    //         ->deleteJson(route('tasks.forceDelete', ['id' => $task->id]))
    //         ->assertStatus(200);
    // }

    /** @test */
    public function an_admin_can_create_a_task()
    {
        $data = [
            'title' => 'Test Task',
            'type' => 'feature',
            'description' => 'Test task description',
            'priority' => 'high',
            'status' => 'pending',
            'assigned_to' => null,
            'due_date' =>  Carbon::now()->addDays(7)->format('d-m-Y'),
        ];

        $this->actingAs($this->admin, 'api')
            ->postJson(route('tasks.store'), $data)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Test Task']);
    }

    /** @test */
    public function a_manager_can_assign_a_task_to_a_user()
    {
        $task = Task::factory()->create(
            [
                'title' => 'Test Task',
                'type' => 'feature',
                'description' => 'Test task description',
                'priority' => 'high',
                'status' => 'pending',
                'assigned_to' => null,
                'due_date' =>  Carbon::now()->addDays(7)->format('d-m-Y'),
            ]
        );

        $this->actingAs($this->manager, 'api')
            ->postJson(route('assignTask', ['task' => $task->id]), [
                'assigned_to' => $this->developer->id,
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['assigned_to' => $this->developer->id]);
    }

    /** @test */
    public function a_manager_can_upload_attachments_for_a_task()
    {
        // Create a task
        $task = Task::factory()->create([
            'title' => 'Test Task',
            'type' => 'feature',
            'description' => 'Test task description',
            'priority' => 'high',
            'status' => 'pending',
            'assigned_to' => null,
            'due_date' =>  Carbon::now()->addDays(7)->format('d-m-Y'),
        ]);

        // Create a fake image file
        $file = UploadedFile::fake()->image('test-file.jpg');

        // Mock the VirusTotal API response to simulate a successful scan
        Http::fake([
            'https://www.virustotal.com/api/v3/files' => Http::response(['data' => ['id' => 'fake-analysis-id']]),
            'https://www.virustotal.com/api/v3/analyses/fake-analysis-id' => Http::response([
                'data' => [
                    'attributes' => [
                        'status' => 'completed',
                        'stats' => ['malicious' => 0]
                    ]
                ]
            ]),
        ]);

        // Act as the manager and make the file upload request
        $response = $this->actingAs($this->manager, 'api')
            ->post(route('uploadAttachment', ['task' => $task->id]), [
                'file' => $file,
            ]);

        // Assert the response status is 201 (Created)
        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'File uploaded successfully.']);
    }


    /** @test */
    public function a_developer_can_change_task_status()
    {
        $task = Task::factory()->create(
            [
                'title' => 'Test Task',
                'type' => 'feature',
                'description' => 'Test task description',
                'priority' => 'high',
                'status' => 'pending',
                'assigned_to' => $this->developer->id,
                'due_date' =>  Carbon::now()->addDays(7)->format('d-m-Y'),
            ]
        );

        $this->actingAs($this->developer, 'api')
            ->putJson(route('tasks.statusChange', ['task' => $task->id]), [
                'status' => 'completed',
            ])
            ->assertStatus(200);
    }

    /** @test */
    public function any_role_can_view_tasks()
    {
        $task = Task::factory()->create(
            [
                'title' => 'Test Task',
                'type' => 'feature',
                'description' => 'Test task description',
                'priority' => 'high',
                'status' => 'pending',
                'assigned_to' => $this->developer->id,
                'due_date' =>  Carbon::now()->addDays(7)->format('d-m-Y'),
            ]
        );

        $this->actingAs($this->admin, 'api')
            ->getJson(route('tasks.show', ['task' => $task->id]))
            ->assertStatus(200);

        $this->actingAs($this->manager, 'api')
            ->getJson(route('tasks.show', ['task' => $task->id]))
            ->assertStatus(200);

        $this->actingAs($this->developer, 'api')
            ->getJson(route('tasks.show', ['task' => $task->id]))
            ->assertStatus(200);
    }

    // /** @test */
    // public function any_role_can_view_blocked_tasks()
    // {
    //     $task = Task::factory()->create(
    //         [
    //             'title' => 'Test Task',
    //             'type' => 'bug',
    //             'description' => 'Test task description',
    //             'priority' => 'high',
    //             'status' => 'blocked',
    //             'assigned_to' => $this->developer->id,
    //             'due_date' =>  Carbon::now()->addDays(7)->format('d-m-Y'),
    //         ]
    //     );
    //     $this->actingAs($this->admin, 'api')
    //         ->getJson(route('tasks.blockedTasks'))
    //         ->assertStatus(200);

    //     $this->actingAs($this->manager, 'api')
    //         ->getJson(route('tasks.blockedTasks'))
    //         ->assertStatus(200);

    //     $this->actingAs($this->developer, 'api')
    //         ->getJson(route('tasks.blockedTasks'))
    //         ->assertStatus(200);
    // }

    /** @test */
    public function non_admin_users_cannot_delete_tasks()
    {
        $task = Task::factory()->create(
            [
                'title' => 'Test Task',
                'type' => 'feature',
                'description' => 'Test task description',
                'priority' => 'high',
                'status' => 'pending',
                'assigned_to' => $this->developer->id,
                'due_date' =>  Carbon::now()->addDays(7)->format('d-m-Y'),
            ]
        );

        $this->actingAs($this->manager, 'api')
            ->deleteJson(route('tasks.destroy', ['task' => $task->id]))
            ->assertStatus(403);

        $this->actingAs($this->developer, 'api')
            ->deleteJson(route('tasks.destroy', ['task' => $task->id]))
            ->assertStatus(403);
    }

    /** @test */
    public function non_manager_users_cannot_assign_or_reassign_tasks()
    {
        $task = Task::factory()->create(
            [
                'title' => 'Test Task',
                'type' => 'feature',
                'description' => 'Test task description',
                'priority' => 'high',
                'status' => 'pending',
                'assigned_to' => $this->developer->id,
                'due_date' =>  Carbon::now()->addDays(7)->format('d-m-Y'),
            ]
        );

        $this->actingAs($this->developer, 'api')
            ->putJson(route('reassignTask', ['task' => $task->id]), [
                'assigned_to' => $this->developer->id,
            ])
            ->assertStatus(403);

        $this->actingAs($this->developer, 'api')
            ->putJson(route('reassignTask', ['task' => $task->id]), [
                'assigned_to' => $this->manager->id,
            ])
            ->assertStatus(403);
    }
}
