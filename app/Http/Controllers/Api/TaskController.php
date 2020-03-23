<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
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
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->role == constants('user.role.admin')) {
            return TaskResource::collection(Task::orderByDesc('updated_at')->get());
        } else if ($user->role == constants('user.role.manager')) {
            $member_in_group = User::where('group_id', $user->group->id)->get('id');
            return TaskResource::collection(Task::whereIn('assignee_id', $member_in_group)->orderByDesc('updated_at')->get());
        }
    }

    /**
     * Display a listing of the given tasks.
     *
     * @return \Illuminate\Http\Response
     */
    public function given()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return TaskResource::collection(Task::where('assignee_id', $user->id)->orderByDesc('updated_at')->get());
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
        $status = $isMember ? constants('task.status.pending_approval') : (date("Y-m-d H:i:s") >= $request->get('start_at') ? constants('task.status.ongoing') : constants('task.status.not_started'));
        $model->create($request->merge([
            'status'      => $status,
            'assigner_id' => $user->id,
            'assignee_id' => $assignee_id,
            'creator_id'  => $user->id,
        ])->except([$isMember ? 'assigner_id' : '']));
        return response()->json([
            'message' => 'Create task successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        return (new TaskResource($task));
    }

    /**
     * Approve the task.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, Task $task)
    {
        if ($task->status != constants('task.status.pending_approval')) {
            return response()->json([
                'message' => 'This task cannot be approved'],
            );
        }
        $user = JWTAuth::parseToken()->authenticate();
        $task->update($request->merge([
            'approver_id' => $user->id,
            'approved_at' => date("Y-m-d H:i:s"),
            'updater_id'  => $user->id,
            'status'     => date("Y-m-d H:i:s") >= $request->get('start_at') ? constants('task.status.ongoing') : constants('task.status.not_started')
        ])->all());
        return response()->json([
            'message' => 'Approve task successfully'
        ]);
    }

    /**
     * Reject the task.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, Task $task)
    {
        if ($task->status != constants('task.status.pending_approval')) {
            return response()->json([
                'message' => 'This task cannot be rejected'],
            );
        }
        $user = JWTAuth::parseToken()->authenticate();
        $task->update($request->merge([
            'updater_id'  => $user->id,
            'approver_id' => $user->id,
            'approved_at' => date("Y-m-d H:i:s"),
            'status'      => constants('task.status.rejected')
        ])->all());
        return response()->json([
            'message' => 'Reject task successfully'
        ]);
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
        if ($task->status != constants('task.status.not_started')) {
            return response()->json([
                'message' => 'This task cannot be updated'],
            );
        }
        $user = JWTAuth::parseToken()->authenticate();
        $task->update($request->merge([
            'updater_id' => $user->id,
            'status'     => date("Y-m-d H:i:s") >= $request->get('start_at') ? constants('task.status.ongoing') : constants('task.status.not_started')
        ])->all());
        return response()->json([
            'message' => 'Update task successfully'
        ]);
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
            return response()->json([
                'message' => 'This task cannot be commit'],
            );
        }
        $user = JWTAuth::parseToken()->authenticate();
        $task->update($request->merge([
            'updater_id'   => $user->id,
            'committed_at' => date("Y-m-d H:i:s"),
            'status'       => constants('task.status.committed')
        ])->except([$request->get('start_at') < date("Y-m-d H:i:s") ? '' : 'status']));
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
            return response()->json([
                'message' => 'This task cannot be evaluated'],
            );
        }
        $user = JWTAuth::parseToken()->authenticate();
        $task->update($request->merge([
            'commenter_id' => $user->id,
            'evaluated_at' => '',
            'updater_id'   => $user->id,
        ])->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json([
            'message' => 'Delete task successfully'
        ]);
    }
}
