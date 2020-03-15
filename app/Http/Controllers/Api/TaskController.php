<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\TaskRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TaskRequest $request, Task $model)
    {
        $model->create($request->all());
        return response()->json(
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Approve the task.
     *
     * @param  \Illuminate\Http\TaskRequest  $request
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function approve(TaskRequest $request, Task $task)
    {
        //
    }

    /**
     * Update the task.
     *
     * @param  \Illuminate\Http\TaskRequest  $request
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function update(TaskRequest $request, Task $task)
    {
        //
    }

    /**
     * Commit the task.
     *
     * @param  \Illuminate\Http\TaskRequest  $request
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function commit(TaskRequest $request, Task $task)
    {
        //
    }

    /**
     * Evaluate the task.
     *
     * @param  \Illuminate\Http\TaskRequest  $request
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function evaluate(TaskRequest $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        //
    }
}
