<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Tasks\AssignTaskRequest;
use App\Http\Requests\Api\V1\Tasks\StoreTaskRequest;
use App\Http\Requests\Api\V1\Tasks\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TaskController
{
    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request)
    {
        $tasks = $request->user()
            ->tasks()
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->priority, function ($query) use ($request) {
                $query->where('priority', $request->priority);
            })
            ->when($request->due_date, function ($query) use ($request) {
                $query->whereDate('due_date', $request->due_date);
            })
            ->when($request->sort, function ($query) use ($request) {
                $sortField = $request->sort;
                $direction = 'asc';

                if (str_starts_with($sortField, '-')) {
                    $direction = 'desc';
                    $sortField = substr($sortField, 1);
                }

                $query->orderBy($sortField, $direction);
            })
            ->with(['user:id,name,email', 'assignees:id,name,email'])
            ->simplePaginate($request->input('per_page', 10));

        return response()->success($tasks, 'Successfully retrieved tasks');
    }

    /**
     * Store a newly created task
     * @param StoreTaskRequest $request
     * @return mixed|JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $request->user()->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'status' => $request->status ?? 'Todo',
            'priority' => $request->priority ?? 'Low',
        ]);
        $task->load(['user:id,name,email', 'assignees:id,name,email']);

        return response()->success(
            $task,
            'Task created successfully',
            201
        );
    }

    /**
     * Show the specified task.
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        // Authorize that this task belongs to the authenticated user
        if ($task->user_id !== auth()->id()) {
            return response()->error([], 'you are not authorized to view this task', 403);
        }
        $task->load(['user:id,name,email', 'assignees:id,name,email']);

        return response()->success($task, 'Task retrieved successfully');
    }

    /**
     * Update the specified task
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse|mixed
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());

        return response()->success($task, 'Task updated successfully');
    }

    /**
     * Delete the specified task
     * @param Task $task
     * @return JsonResponse|mixed
     */
    public function destroy(Task $task): JsonResponse
    {
        // Authorize that this task belongs to the authenticated user
        if ($task->user_id !== auth()->id()) {
            return response()->error([], 'you are not authorized to delete this task', 403);
        }

        $task->delete();

        return response()->success([], 'Task deleted successfully');
    }

    /**
     * Assign a task to a user
     * @param AssignTaskRequest $request
     * @param Task $task
     * @return JsonResponse|mixed
     */
    public function assign(AssignTaskRequest $request, Task $task): JsonResponse
    {

        $task->assignees()->attach($request->user_id);

        return response()->success(
            $task->load('assignees:id,name,email'),
            'Task assigned successfully'
        );
    }
}
