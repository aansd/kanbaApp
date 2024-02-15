<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class TaskTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
    
   
   
    
    private $mockedUsers = [];
    private $mockedTasks = [];

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
        User::factory()->create();
        $user1 = User::first();
        $user2 = User::where('id', '!=', $user1->id)->first();
        array_push($this->mockedUsers, $user1, $user2);
        $this->actingAs($user1);

        $tasks = [
            [
                'name' => 'Task 1',
                'status' => Task::STATUS_NOT_STARTED,
                'user_id' => $user1->id,
            ],
            [
                'name' => 'Task 2',
                'status' => Task::STATUS_IN_PROGRESS,
                'user_id' => $user1->id,
            ],
            [
                'name' => 'Task 3',
                'status' => Task::STATUS_COMPLETED,
                'user_id' => $user1->id,
            ],
            [
                'name' => 'Task 4',
                'status' => Task::STATUS_COMPLETED,
                'user_id' => $user2->id,
            ],
        ];

        Task::insert($tasks);

        $this->mockedTasks = Task::with('user', 'files')
            ->get()
            ->toArray();
            
    }

    public function test_home(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $response->assertViewIs('home');
        $response->assertViewHas('completed_count');
        $response->assertViewHas('uncompleted_count');

        $completed_count = $response->viewData('completed_count');
        $uncompleted_count = $response->viewData('uncompleted_count');

        $this->assertEquals(1, $completed_count);
        $this->assertEquals(2, $uncompleted_count);
    }


    public function test_redirect_not_logged_in_user(): void
    {
        Auth::logout();

        $response = $this->get(route('home'));
        $response->assertStatus(302);
    }

    public function test_index_without_permission(): void
    {
        $response = $this->get(route('tasks.index'));
    
        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');

        $tasks = $response->viewData('tasks')->toArray();

        $expectedTasks = [
            $this->mockedTasks[0],
            $this->mockedTasks[1],
            $this->mockedTasks[2],
        ];

        $this->assertEquals($expectedTasks, $tasks);
    }

    public function test_index_with_right_permission(): void
    {
        Gate::shouldReceive('allows')
            ->with('viewAnyTask', Task::class)
            ->andReturn(true);
        Gate::shouldReceive('any')->andReturn(false);
        Gate::shouldReceive('check')->andReturn(false);

        $response = $this->get(route('tasks.index'));
        $response->assertStatus(200);

        $tasks = $response->viewData('tasks');

        $expectedTasks = $this->mockedTasks;

        $this->assertEquals($expectedTasks, $tasks->toArray());
    }

    public function test_create()
    {
    
        $response = $this->get(route('tasks.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.create');
        $response->assertViewHas('pageTitle');
    
        $pageTitle = $response->viewData('pageTitle');
    
        $this->assertEquals('Create Task', $pageTitle);
    }
    
    public function test_store_without_file()
    {
    $newTask = [
        'name' => 'New Task',
        'detail' => 'New Task Detail',
        'due_date' => date('Y-m-d', time()),
        'status' => Task::STATUS_NOT_STARTED,
    ];

    $response = $this->post(route('tasks.store'), $newTask);

    $response->assertStatus(302);
    $response->assertRedirect(route('tasks.index'));
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('tasks', $newTask);
    }

    public function test_store_with_file(): void
    {
        Storage::fake('public');

        $newTask = [
            'name' => 'New Task',
            'detail' => 'New Task detail',
            'due_date' => date('Y-m-d', time()),
            'status' => Task::STATUS_IN_PROGRESS,
        ];

       
        $file = UploadedFile::fake()->image('test_image.png');

       
        $response = $this->post(
            route('tasks.store'),
            array_merge($newTask, ['file' => $file])
        );

        $response->assertStatus(302);
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', $newTask);

      
        $task = Task::where('name', 'New Task')->first();
        $this->assertNotNull($task->files);

       
        $filePath = $task->files[0]->path;
        Storage::disk('public')->exists($filePath);
        
        

    }

    public function test_store_invalid_request(): void
    {
        $response = $this->post(route('tasks.store'), [
            'detail' => 'New Task',
        ]);
    
        $response->assertSessionHasErrors(['name', 'due_date', 'status']);
    }

    public function test_edit_task_owner(): void
    {
        $user1 = $this->mockedUsers[0];
        $taskOwnedByUser1 = Task::where('user_id', $user1->id)->first();
    
        $response = $this->get(route('tasks.edit', ['id' => $taskOwnedByUser1->id]));
    
        $response->assertStatus(200);
        $response->assertViewIs('tasks.edit');
        $response->assertViewHas('task', $taskOwnedByUser1);
    }

    public function test_edit_with_right_permission(): void
    {
    $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();

    Gate::shouldReceive('allows')
        ->with('updateAnyTask', Task::class)
        ->andReturn(true);

    $this->actingAs($this->mockedUsers[0]);

    $response = $this->get(route('tasks.edit', ['id' => $taskOwnedByUser2->id]));

    $response->assertStatus(200);

    $response->assertViewHas('task', $taskOwnedByUser2);
    }


    public function test_edit_unauthorized_user(): void
    {

    $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();
    $this->actingAs($this->mockedUsers[0]);
    $response = $this->get(route('tasks.edit', ['id' => $taskOwnedByUser2->id]));

    $response->assertStatus(403);
    }
        
    public function test_update_task_owner(): void
    {
    
    $taskOwnedByUser1 = Task::where('user_id', $this->mockedUsers[0]->id)->first();

    $updatedData = [
        'name' => 'Updated Task Name',
        'detail' => 'Updated Task Detail',
        'due_date' => '2024-01-10', 
        'status' => Task::STATUS_COMPLETED,
    ];

   
    $this->actingAs($this->mockedUsers[0]);

    $response = $this->put(route('tasks.update', ['id' => $taskOwnedByUser1->id]), $updatedData);

    $response->assertStatus(200);

    $this->assertDatabaseHas('tasks', $updatedData);
    }

    public function test_update_with_right_permission(): void
    {
    $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();

    $updatedData = [
        'name' => 'Updated Task Name',
        'detail' => 'Updated Task Detail',
        'due_date' => '2024-01-10',
        'status' => Task::STATUS_COMPLETED,
    ];

    Gate::shouldReceive('allows')
        ->with('updateAnyTask', Task::class)
        ->andReturn(true);

    $this->actingAs($this->mockedUsers[0]);

    $response = $this->put(route('tasks.update', ['id' => $taskOwnedByUser2->id]), $updatedData);

    $response->assertStatus(200);

    $this->assertDatabaseHas('tasks', $updatedData);
    }

    public function test_update_unauthorized_user(): void
    {
    $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();


    $updatedData = [
        'name' => 'Updated Task Name',
        'detail' => 'Updated Task Detail',
        'due_date' => '2024-01-10',
        'status' => Task::STATUS_COMPLETED,
    ];

    $this->actingAs($this->mockedUsers[0]);

    $response = $this->put(route('tasks.update', ['id' => $taskOwnedByUser2->id]), $updatedData);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('tasks', $updatedData);
    }

    public function test_delete_task_owner(): void
    {
    $taskOwnedByUser1 = Task::where('user_id', $this->mockedUsers[0]->id)->first();

    $this->actingAs($this->mockedUsers[0]);

    $response = $this->get(route('tasks.delete', ['id' => $taskOwnedByUser1->id]));

    $response->assertStatus(200);

    $response->assertViewHas('task', $taskOwnedByUser1);
    }

    public function test_delete_with_right_permission(): void
    {
    $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();

    Gate::shouldReceive('allows')
        ->with('deleteAnyTask', Task::class)
        ->andReturn(true);
    $this->actingAs($this->mockedUsers[0]);

  
    $response = $this->get(route('tasks.delete', ['id' => $taskOwnedByUser2->id]));

    $response->assertStatus(200);

    $response->assertViewHas('task', $taskOwnedByUser2);
    }

    public function test_delete_unauthorized_user(): void
    {
        $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();

        $this->actingAs($this->mockedUsers[0]);

        $response = $this->get(route('tasks.delete', ['id' => $taskOwnedByUser2->id]));

        $response->assertStatus(403);
    }

    public function test_destroy_task_owner(): void
    {
        $taskOwnedByUser1 = Task::where('user_id', $this->mockedUsers[0]->id)->first();
        $response = $this->delete(route('tasks.destroy', ['id' => $taskOwnedByUser1->id]));

        $response->assertStatus(302);//200

        $this->assertDatabaseMissing('tasks', ['id' => $taskOwnedByUser1->id]);
    }

    public function test_destroy_with_right_permission(): void
    {
    $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();

    Gate::shouldReceive('allows')
        ->with('deleteAnyTask', Task::class)
        ->andReturn(true);

    $this->actingAs($this->mockedUsers[0]);

    $response = $this->delete(route('tasks.destroy', ['id' => $taskOwnedByUser2->id]));

    $response->assertStatus(200);

    $this->assertDatabaseMissing('tasks', ['id' => $taskOwnedByUser2->id]);
    }

    public function test_destroy_unauthorized_user(): void
    {
    $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();

    $this->actingAs($this->mockedUsers[0]);

    $response = $this->delete(route('tasks.destroy', ['id' => $taskOwnedByUser2->id]));

    $response->assertStatus(403);

    $this->assertDatabaseHas('tasks', ['id' => $taskOwnedByUser2->id]);
    }
    
    public function test_progress_without_permission(): void
    {
    $this->actingAs($this->mockedUsers[0]);

    $tasksOwnedByUser1 = Task::where('user_id', $this->mockedUsers[0]->id)->get();

    $tasksOwnedByOtherUsers = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->get();

    $response = $this->get(route('tasks.progress'));

    $response->assertStatus(200);

    foreach ($tasksOwnedByUser1 as $task) {
        $response->assertSee($task->name);
    }

    foreach ($tasksOwnedByOtherUsers as $task) {
        $response->assertDontSee($task->name);
    }

    $response->assertViewHas('tasks');
    $tasks = $response->viewData('tasks');
    $this->assertArrayHasKey('not_started', $tasks);
    $this->assertArrayHasKey('in_progress', $tasks);
    $this->assertArrayHasKey('in_review', $tasks);
    $this->assertArrayHasKey('completed', $tasks);
    }

    public function test_progress_with_right_permission(): void
    {
        Gate::shouldReceive('allows')
        ->with('viewAnyTask', Task::class)
        ->andReturn(true);
    Gate::shouldReceive('any')->andReturn(false);
    Gate::shouldReceive('check')->andReturn(false);

    $this->actingAs($this->mockedUsers[0]);

    $allTasks = Task::all();

    $response = $this->get(route('tasks.progress'));

    $response->assertStatus(200);

    foreach ($allTasks as $task) {
        $response->assertSee($task->name);
    }

    $response->assertViewHas('tasks');
    $tasks = $response->viewData('tasks');
    $this->assertArrayHasKey('not_started', $tasks);
    $this->assertArrayHasKey('in_progress', $tasks);
    $this->assertArrayHasKey('in_review', $tasks);
    $this->assertArrayHasKey('completed', $tasks);
    }

    public function test_move_task_owner(): void
    {
    $this->actingAs($this->mockedUsers[0]);

    $taskOwnedByUser1 = Task::where('user_id', $this->mockedUsers[0]->id)->first();

    $newStatus = Task::STATUS_IN_PROGRESS;
 
    $response = $this->patch(route('tasks.move', ['id' => $taskOwnedByUser1->id]), ['status' => $newStatus]);

    
    $response->assertStatus(302);//200

    $taskOwnedByUser1->refresh();

    $this->assertEquals($newStatus, $taskOwnedByUser1->status);
    }   

    public function test_move_with_right_permission(): void
    {
    Gate::shouldReceive('allows')
        ->with('updateAnyTask', Task::class)
        ->andReturn(true);
    Gate::shouldReceive('any')->andReturn(false);
    Gate::shouldReceive('check')->andReturn(false);

    $this->actingAs($this->mockedUsers[0]);

    $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();

    $newStatus = Task::STATUS_IN_PROGRESS;

    $response = $this->patch(route('tasks.move', ['id' => $taskOwnedByUser2->id]), ['status' => $newStatus]);

    $response->assertStatus(200);

    $taskOwnedByUser2->refresh();

    $this->assertEquals($newStatus, $taskOwnedByUser2->status);
    }

    public function test_move_unauthorized_user(): void
    {
    $this->actingAs($this->mockedUsers[0]);

    $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();

    $newStatus = Task::STATUS_IN_PROGRESS;

    $response = $this->patch(route('tasks.move', ['id' => $taskOwnedByUser2->id]), ['status' => $newStatus]);

    $response->assertStatus(403);

    $taskOwnedByUser2->refresh();

    $this->assertNotEquals($newStatus, $taskOwnedByUser2->status);
    }

    public function test_complete_task_owner(): void
    {
    $this->actingAs($this->mockedUsers[0]);

    $taskOwnedByUser1 = Task::where('user_id', $this->mockedUsers[0]->id)->first();

    $newStatus = Task::STATUS_COMPLETED;

    $response = $this->patch(route('tasks.completed', ['id' => $taskOwnedByUser1->id]));

    $response->assertStatus(302);//200

    $taskOwnedByUser1->refresh();

    $this->assertEquals($newStatus, $taskOwnedByUser1->status);
    }

    public function test_complete_unauthorized_user(): void
    {
    $this->actingAs($this->mockedUsers[0]);

    $taskOwnedByUser2 = Task::where('user_id', '!=', $this->mockedUsers[0]->id)->first();

    $newStatus = Task::STATUS_IN_PROGRESS;

    $response = $this->patch(route('tasks.completed', ['id' => $taskOwnedByUser2->id]));

    $response->assertStatus(403);

    $taskOwnedByUser2->refresh();

    $this->assertNotEquals($newStatus, $taskOwnedByUser2->status);
    }
    
    
}
