<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Services\TaskOrderingService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskOrderingServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskOrderingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->service = new TaskOrderingService();
    }

    public function test_apply_order_assigns_sparse_sort_orders(): void
    {
        $project = Project::factory()->create();
        $t1 = Task::factory()->create(['project_id' => $project->id, 'status' => 'backlog', 'sort_order' => 999]);
        $t2 = Task::factory()->create(['project_id' => $project->id, 'status' => 'backlog', 'sort_order' => 999]);
        $t3 = Task::factory()->create(['project_id' => $project->id, 'status' => 'backlog', 'sort_order' => 999]);

        $this->service->applyOrder([$t1->id, $t2->id, $t3->id]);

        $this->assertEquals(100, $t1->fresh()->sort_order);
        $this->assertEquals(200, $t2->fresh()->sort_order);
        $this->assertEquals(300, $t3->fresh()->sort_order);
    }

    public function test_apply_order_respects_given_sequence(): void
    {
        $project = Project::factory()->create();
        $t1 = Task::factory()->create(['project_id' => $project->id, 'status' => 'todo', 'sort_order' => 100]);
        $t2 = Task::factory()->create(['project_id' => $project->id, 'status' => 'todo', 'sort_order' => 200]);

        // Reverse the order
        $this->service->applyOrder([$t2->id, $t1->id]);

        $this->assertEquals(100, $t2->fresh()->sort_order);
        $this->assertEquals(200, $t1->fresh()->sort_order);
    }

    public function test_rebalance_reassigns_clean_orders(): void
    {
        $project = Project::factory()->create();
        $t1 = Task::factory()->create(['project_id' => $project->id, 'status' => 'backlog', 'sort_order' => 1]);
        $t2 = Task::factory()->create(['project_id' => $project->id, 'status' => 'backlog', 'sort_order' => 2]);
        $t3 = Task::factory()->create(['project_id' => $project->id, 'status' => 'backlog', 'sort_order' => 3]);

        $this->service->rebalance($project, 'backlog');

        $this->assertEquals(100, $t1->fresh()->sort_order);
        $this->assertEquals(200, $t2->fresh()->sort_order);
        $this->assertEquals(300, $t3->fresh()->sort_order);
    }

    public function test_rebalance_preserves_relative_order(): void
    {
        $project = Project::factory()->create();
        $t1 = Task::factory()->create(['project_id' => $project->id, 'status' => 'in_progress', 'sort_order' => 50]);
        $t2 = Task::factory()->create(['project_id' => $project->id, 'status' => 'in_progress', 'sort_order' => 51]);

        $this->service->rebalance($project, 'in_progress');

        $this->assertLessThan($t2->fresh()->sort_order, $t1->fresh()->sort_order);
    }
}
