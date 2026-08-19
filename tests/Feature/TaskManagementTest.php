<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_tasks_get_the_next_priority_in_their_scope(): void
    {
        $project = Project::factory()->create();
        Task::factory()->create(['project_id' => $project->id, 'priority' => 1]);
        Task::factory()->create(['project_id' => $project->id, 'priority' => 2]);

        $this->post(route('tasks.store'), [
            'name' => 'Third task',
            'project_id' => $project->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'name' => 'Third task',
            'project_id' => $project->id,
            'priority' => 3,
        ]);

        $task = Task::where('name', 'Third task')->firstOrFail();
        $this->assertNotNull($task->created_at);
        $this->assertNotNull($task->updated_at);
    }

    public function test_reorder_rewrites_priorities_from_one(): void
    {
        $project = Project::factory()->create();
        $tasks = Task::factory(3)->sequence(
            ['priority' => 1],
            ['priority' => 2],
            ['priority' => 3],
        )->for($project)->create();

        $this->postJson(route('tasks.reorder'), [
            'project_id' => $project->id,
            'task_ids' => $tasks->reverse()->modelKeys(),
        ])->assertOk();

        $this->assertSame(
            [1, 2, 3],
            Task::where('project_id', $project->id)->orderBy('priority')->pluck('priority')->all()
        );
        $this->assertSame($tasks[2]->id, Task::where('priority', 1)->first()->id);
    }

    public function test_reorder_ignores_tasks_from_another_project(): void
    {
        $project = Project::factory()->create();
        $otherProject = Project::factory()->create();
        $tasks = Task::factory(2)->sequence(
            ['priority' => 1],
            ['priority' => 2],
        )->for($project)->create();
        $otherTask = Task::factory()->create([
            'project_id' => $otherProject->id,
            'priority' => 1,
        ]);

        $this->postJson(route('tasks.reorder'), [
            'project_id' => $project->id,
            'task_ids' => [$tasks[1]->id, $otherTask->id, $tasks[0]->id],
        ])->assertOk();

        $this->assertSame(
            [1, 2],
            Task::where('project_id', $project->id)->orderBy('priority')->pluck('priority')->all()
        );
        $this->assertDatabaseHas('tasks', [
            'id' => $otherTask->id,
            'priority' => 1,
        ]);
        $this->assertSame(
            $tasks[1]->id,
            Task::where('project_id', $project->id)->where('priority', 1)->first()->id
        );
    }

    public function test_reorder_rewrites_priorities_in_the_unassigned_scope(): void
    {
        $tasks = Task::factory(3)->sequence(
            ['priority' => 1],
            ['priority' => 2],
            ['priority' => 3],
        )->create();

        $this->postJson(route('tasks.reorder'), [
            'project_id' => null,
            'task_ids' => $tasks->reverse()->modelKeys(),
        ])->assertOk();

        $this->assertSame(
            [1, 2, 3],
            Task::whereNull('project_id')->orderBy('priority')->pluck('priority')->all()
        );
        $this->assertSame($tasks[2]->id, Task::whereNull('project_id')->where('priority', 1)->first()->id);
    }

    public function test_deleting_a_task_closes_the_priority_gap(): void
    {
        $project = Project::factory()->create();
        $tasks = Task::factory(3)->sequence(
            ['priority' => 1],
            ['priority' => 2],
            ['priority' => 3],
        )->for($project)->create();

        $this->delete(route('tasks.destroy', $tasks[1]))->assertRedirect();

        $this->assertSame(
            [1, 2],
            Task::where('project_id', $project->id)->orderBy('priority')->pluck('priority')->all()
        );
        $this->assertDatabaseHas('tasks', ['id' => $tasks[2]->id, 'priority' => 2]);
    }

    public function test_project_filter_only_returns_tasks_in_that_project(): void
    {
        $project = Project::factory()->create();
        $otherProject = Project::factory()->create();
        Task::factory()->create(['name' => 'Included task', 'project_id' => $project->id]);
        Task::factory()->create(['name' => 'Excluded task', 'project_id' => $otherProject->id]);

        $this->get(route('tasks.index', ['project' => $project->id]))
            ->assertOk()
            ->assertSee('Included task')
            ->assertDontSee('Excluded task');
    }

    public function test_junk_project_filter_falls_back_to_all_tasks(): void
    {
        $this->get(route('tasks.index', ['project' => 'abc']))
            ->assertOk()
            ->assertSee('All tasks');
    }

    public function test_moving_a_task_resequences_both_projects(): void
    {
        $source = Project::factory()->create();
        $destination = Project::factory()->create();
        $sourceTasks = Task::factory(3)->sequence(
            ['priority' => 1],
            ['priority' => 2],
            ['priority' => 3],
        )->for($source)->create();
        Task::factory(2)->sequence(
            ['priority' => 1],
            ['priority' => 2],
        )->for($destination)->create();

        $this->put(route('tasks.update', $sourceTasks[1]), [
            'name' => $sourceTasks[1]->name,
            'project_id' => $destination->id,
        ])->assertRedirect();

        $this->assertSame(
            [1, 2],
            Task::where('project_id', $source->id)->orderBy('priority')->pluck('priority')->all()
        );
        $this->assertSame(
            [1, 2, 3],
            Task::where('project_id', $destination->id)->orderBy('priority')->pluck('priority')->all()
        );
    }
}
