<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

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
     * Display a listing of the given tasks.
     *
     * @return \Illuminate\Http\Response
     */
    public function given()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return TaskResource::collection(Task::where('assignee_id', $user->id)->get())
                ->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Display a listing of the handout tasks.
     *
     * @return \Illuminate\Http\Response
     */
    public function handout()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return TaskResource::collection(Task::where('assigner_id', $user->id)->get())
                ->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Display a listing of the approved tasks.
     *
     * @return \Illuminate\Http\Response
     */
    public function approved()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return TaskResource::collection(Task::where('approver_id', $user->id)->get())
                ->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\TaskRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TaskRequest $request, Task $model)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $isMember = $user->role == constants('user.role.member');
        $assignee_id = $isMember ? $user->id : $request->get('assignee_id');
        $status = $isMember ? 0 : 3;

        $model->create($request->merge([
            'status'      => $status,
            'assigner_id' => $user->id,
            'assignee_id' => $assignee_id,
            'creator_id'  => $user->id,
        ])->except([$isMember ? 'assigner_id' : '']));
        return response()->json(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        return (new TaskResource($task))->response()->setStatusCode(Response::HTTP_OK);
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
        if ($task->status != constants('task.status.pending_approval')) {
            return response()->json(
                ['message' => 'This task cannot be approved'],
                Response::HTTP_OK
            );
        }
        $user = JWTAuth::parseToken()->authenticate();
        $task->update($request->merge([
            'approver_id' => $user->id,
            'approved_at' => date("Y-m-d H:i:s"),
            'updater_id'  => $user->id,
        ])->all());
        return response()->json(Response::HTTP_OK);
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
        if ($task->status != constants('task.status.not_stated')) {
            return response()->json(
                ['message' => 'This task cannot be updated'],
                Response::HTTP_OK
            );
        }
        $user = JWTAuth::parseToken()->authenticate();
        $task->update($request->merge([
            'updater_id' => $user->id,
            'status'     => constants('task.status.not_stated')
        ])->except([$request->get('start_at') < date("Y-m-d H:i:s") ? '' : 'status']));
        return response()->json(Response::HTTP_OK);
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
        if ($task->status != constants('task.status.ongoing')) {
            return response()->json(
                ['message' => 'This task cannot be commit'],
                Response::HTTP_OK
            );
        }
        $user = JWTAuth::parseToken()->authenticate();
        $task->update($request->merge([
            'updater_id'   => $user->id,
            'committed_at' => date("Y-m-d H:i:s"),
            'status'       => constants('task.status.committed')
        ])->except([$request->get('start_at') < date("Y-m-d H:i:s") ? '' : 'status']));
        return response()->json(Response::HTTP_OK);
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
        if ($task->status != constants('task.status.committed')) {
            return response()->json(
                ['message' => 'This task cannot be evaluated'],
                Response::HTTP_OK
            );
        }
        $user = JWTAuth::parseToken()->authenticate();
        $task->update($request->merge([
            'commenter_id' => $user->id,
            'evaluated_at' => '',
            'updater_id'   => $user->id,
        ])->all());
        return response()->json(Response::HTTP_OK);
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
