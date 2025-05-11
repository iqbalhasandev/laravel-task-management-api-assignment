<?php

namespace Tests\Feature\API\V1;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $headers;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and generate token
        $this->user = User::factory()->create([
            'email' => 'taskuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Get token via login
        $response = $this->postJson('/api/v1/login', [
            'email' => 'taskuser@example.com',
            'password' => 'password123',
        ]);

        $this->token = $response->json('data.token');
        $this->headers = ['Authorization' => "Bearer {$this->token}"];
    }

    /**
     * Test creating a new task
     */
    public function test_user_can_create_task(): void
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'This is a test task description',
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => TaskStatus::TODO->value,
            'priority' => TaskPriority::MEDIUM->value,
        ];

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'due_date',
                    'status',
                    'priority',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'user',
                    'assignees',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => [
                    'title' => $taskData['title'],
                    'description' => $taskData['description'],
                    'status' => $taskData['status'],
                    'priority' => $taskData['priority'],
                    'user_id' => $this->user->id,
                ],
            ]);

        // Check the database
        $this->assertDatabaseHas('tasks', [
            'title' => $taskData['title'],
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test validation for task creation
     */
    public function test_task_creation_requires_title(): void
    {
        $response = $this->withHeaders($this->headers)
            ->postJson('/api/v1/tasks', [
                'description' => 'This is a test without title',
            ]);
        $response->assertStatus(422);
    }

    /**
     * Test listing all tasks for a user
     */
    public function test_user_can_list_their_tasks(): void
    {
        // Create some tasks for the user
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully retrieved tasks',
            ]);

        // Check that we get back 3 tasks
        $this->assertCount(3, $response->json('data.data'));
    }

    /**
     * Test getting a specific task
     */
    public function test_user_can_view_specific_task(): void
    {
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'View Specific Task Test',
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'due_date',
                    'status',
                    'priority',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'user',
                    'assignees',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Task retrieved successfully',
                'data' => [
                    'id' => $task->id,
                    'title' => 'View Specific Task Test',
                    'user_id' => $this->user->id,
                ],
            ]);
    }

    /**
     * Test user cannot view another user's task
     */
    public function test_user_cannot_view_others_task(): void
    {
        // Create another user and a task for them
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/v1/tasks/{$otherTask->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'you are not authorized to view this task',
            ]);
    }

    /**
     * Test updating a task
     */
    public function test_user_can_update_their_task(): void
    {
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Title',
            'status' => TaskStatus::TODO->value,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'status' => TaskStatus::IN_PROGRESS->value,
        ];

        $response = $this->withHeaders($this->headers)
            ->putJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => [
                    'id' => $task->id,
                    'title' => 'Updated Title',
                    'status' => TaskStatus::IN_PROGRESS->value,
                ],
            ]);

        // Check the database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }

    /**
     * Test deleting a task
     */
    public function test_user_can_delete_their_task(): void
    {
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->headers)
            ->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully',
                'data' => [],
            ]);

        // Check the database
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test user cannot delete another user's task
     */
    public function test_user_cannot_delete_others_task(): void
    {
        // Create another user and a task for them
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeaders($this->headers)
            ->deleteJson("/api/v1/tasks/{$otherTask->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'you are not authorized to delete this task',
            ]);

        // Check the task still exists
        $this->assertDatabaseHas('tasks', [
            'id' => $otherTask->id,
        ]);
    }

    /**
     * Test assigning a task to a user
     */
    public function test_user_can_assign_task_to_another_user(): void
    {
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create another user to assign the task to
        $assignee = User::factory()->create();

        $response = $this->withHeaders($this->headers)
            ->postJson("/api/v1/tasks/{$task->id}/assign", [
                'user_id' => $assignee->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task assigned successfully',
            ]);

        // Check pivot table
        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
        ]);
    }

    /**
     * Test cannot assign a task to the same user twice
     */
    public function test_cannot_assign_task_to_same_user_twice(): void
    {
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create another user to assign the task to
        $assignee = User::factory()->create();

        // Assign the task first time
        $this->withHeaders($this->headers)
            ->postJson("/api/v1/tasks/{$task->id}/assign", [
                'user_id' => $assignee->id,
            ]);

        // Attempt to assign the same task to the same user again
        $response = $this->withHeaders($this->headers)
            ->postJson("/api/v1/tasks/{$task->id}/assign", [
                'user_id' => $assignee->id,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test cannot assign another user's task
     */
    public function test_user_cannot_assign_others_task(): void
    {
        // Create another user and a task for them
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        // Create a user to try to assign the task to
        $assignee = User::factory()->create();

        $response = $this->withHeaders($this->headers)
            ->postJson("/api/v1/tasks/{$otherTask->id}/assign", [
                'user_id' => $assignee->id,
            ]);
        $response->assertStatus(500);
    }
}
