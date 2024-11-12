<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use App\Models\Comment;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TaskSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentControllerTest extends TestCase
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

    public function test_any_logged_in_user_can_create_comment_on_task()
    {
        // Step 1: Retrieve the first task created by the TaskSeeder
        $task = Task::first();

        // Step 2: Prepare the comment data
        $commentData1 = [
            'comment' => 'first comment',
        ];
        $commentData2 = [
            'comment' => 'second comment',
        ];
        $commentData3 = [
            'comment' => 'third comment',
        ];

        // Step 3: Add valid comments by different logged-in users
        $this->actingAs($this->developer, 'api')
            ->postJson(route('comments.store', ['task' => $task->id]), $commentData1)
            ->assertStatus(201)
            ->assertJsonFragment(['comment' => 'first comment']);

        $this->actingAs($this->manager, 'api')
            ->postJson(route('comments.store', ['task' => $task->id]), $commentData2)
            ->assertStatus(201)
            ->assertJsonFragment(['comment' => 'second comment']);

        $this->actingAs($this->admin, 'api')
            ->postJson(route('comments.store', ['task' => $task->id]), $commentData3)
            ->assertStatus(201)
            ->assertJsonFragment(['comment' => 'third comment']);
    }

    public function test_user_create_invalid_comment()
    {

        // Step 1: Retrieve the first task created by the TaskSeeder
        $task = Task::first();
        // step2: create an invalid comment { very short comment should return 422 }
        $commentData4 = [
            'comment' => 'f',
        ];
        // Step 3: Add invalid comments by different logged-in users
        $this->actingAs($this->developer, 'api')
            ->postJson(route('comments.store', ['task' => $task->id]), $commentData4)
            ->assertStatus(422)
            ->assertJsonFragment([
                'status' => 'خطأ',
                'message' => 'فشلت عملية التحقق من صحة البيانات.',
            ]);
    }

    public function test_all_users_can_show_comments_of_task()
    {
        //step 1: create some valid comments by different users
        $this->test_any_logged_in_user_can_create_comment_on_task();

        // Step 2: Retrieve the first task created by the TaskSeeder
        $task = Task::first();

        // Step 3: check if any logged in user can view the comments of the task
        $this->actingAs($this->developer, 'api')
            ->getJson(route('comments.index', ['task' => $task->id]),)
            ->assertStatus(200)
            ->assertJsonFragment(['comment' => 'first comment']);

        $this->actingAs($this->manager, 'api')
            ->getJson(route('comments.index', ['task' => $task->id]),)
            ->assertStatus(200)
            ->assertJsonFragment(['comment' => 'second comment']);

        $this->actingAs($this->admin, 'api')
            ->getJson(route('comments.index', ['task' => $task->id]),)
            ->assertStatus(200)
            ->assertJsonFragment(['comment' => 'third comment']);
    }

    public function test_show_specific_comment_of_specific_task_by_any_logged_in_user()
    {

        //step 1: create some valid comments by different users
        $this->test_any_logged_in_user_can_create_comment_on_task();

        // Step 2: Retrieve the first task created by the TaskSeeder
        $task = Task::first();

        //step 3: get first comment 
        $comment = Comment::first();

        //step 4: check if any logged in user can see it 
        $this->actingAs($this->developer, 'api')
            ->getJson(route('comments.show', ['task' => $task->id, 'comment' => $comment->id]),)
            ->assertStatus(200)
            ->assertJsonFragment(['comment' => 'first comment']);
    }

    public function test_show_not_found_comment_of_specific_task_by_any_logged_in_user()
    {

        // Step 1: Retrieve the first task created by the TaskSeeder
        $task = Task::first();

        //step 3: check the unavaible comment should return 404 
        $this->actingAs($this->developer, 'api')
            ->getJson(route('comments.show', ['task' => $task->id, 'comment' => 4]),) // no comment id=4 found for task1
            ->assertStatus(404)
            ->assertJsonFragment(['message' => 'الموديل غير موجود']);
    }

    
}
