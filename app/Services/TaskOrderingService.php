<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;

class TaskOrderingService
{
    const STEP = 100;

    /**
     * Assign sparse sort orders (100, 200, …) to a list of task IDs in the given order.
     *
     * @param int[] $orderedIds
     */
    public function applyOrder(array $orderedIds): void
    {
        $order = self::STEP;
        foreach ($orderedIds as $id) {
            Task::where('id', $id)->update(['sort_order' => $order]);
            $order += self::STEP;
        }
    }

    /**
     * Rebalance all tasks in a column to clean, evenly-spaced sort orders.
     */
    public function rebalance(Project $project, string $status): void
    {
        $ids = $project->tasks()
            ->where('status', $status)
            ->orderBy('sort_order')
            ->pluck('id')
            ->toArray();

        $this->applyOrder($ids);
    }
}
