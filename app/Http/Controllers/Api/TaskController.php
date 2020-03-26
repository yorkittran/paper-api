<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Notification;
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
            $member_in_group = User::where('group_id', $user->group->id)->get('id')->toArray();
            return TaskResource::collection(Task::whereIn('assignee_id', $member_in_group)->orWhere('assignee_id', $user->id)->orderByDesc('updated_at')->get());
        } else if ($user->role == constants('user.role.member')) {
            return TaskResource::collection(Task::where('assignee_id', $user->id)->orderByDesc('updated_at')->get());
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function old()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $old_task_picked = Task::where('old_task', '!=', null)->get('old_task')->toArray();
        if ($user->role == constants('user.role.admin')) {
            return TaskResource::collection(Task::whereNotIn('id', $old_task_picked)->whereIn('status', [constants('task.status.incompleted'), constants('task.status.overdue')])->orderByDesc('updated_at')->get());
        } else if ($user->role == constants('user.role.manager')) {
            $member_in_group = User::where('group_id', $user->group->id)->get('id')->toArray();
            return TaskResource::collection(Task::whereNotIn('id', $old_task_picked)->whereIn('status', [constants('task.status.incompleted'), constants('task.status.overdue')])->whereIn('assignee_id', $member_in_group)->orWhere('assignee_id', $user->id)->orderByDesc('updated_at')->get());
        } else if ($user->role == constants('user.role.member')) {
            return TaskResource::collection(Task::whereNotIn('id', $old_task_picked)->whereIn('status', [constants('task.status.incompleted'), constants('task.status.overdue')])->where('assignee_id', $user->id)->orderByDesc('updated_at')->get());
        }
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

        // Create push data
        $to = [];
        $title =  $request->get('name') . ' | ' . constants('task.status.' . $status) . ' Task';
        $body = '';
        if ($isMember) {
            $admin = User::where('role', constants('user.role.admin'))->first();
            $user->inGroup->manager->push_token ? array_push($to, $user->inGroup->manager->push_token) : true;
            $admin->push_token ? array_push($to, $admin->push_token) : true;
            $body  = 'New task has been created by ' . $user->name . ' and waiting for approval';

            // Create notification record to database
            Notification::create([
                'user_id' => $user->inGroup->manager->id,
                'title'   => $title,
                'content' => $body,
            ]);
            Notification::create([
                'user_id' => $admin->id,
                'title'   => $title,
                'content' => $body,
            ]);
        } else {
            $assignee = User::where('id', $assignee_id)->get('push_token')->first();
            $assignee->push_token ? array_push($to, $assignee->push_token) : true;
            $body = 'You have been assigned to a ' . constants('task.status.' . $status) . ' task by ' . $user->name;

            // Create notification record to database
            Notification::create([
                'user_id' => $assignee_id,
                'title'   => $title,
                'content' => $body,
            ]);
        }
        // Push notification
        $to ? $this->pushToExpo($to, $body, $title) : true;

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
        $status = date("Y-m-d H:i:s") >= $task->start_at ? constants('task.status.ongoing') : constants('task.status.not_started');
        $task->update($request->merge([
            'approver_id' => $user->id,
            'approved_at' => date("Y-m-d H:i:s"),
            'updater_id'  => $user->id,
            'status'      => $status,
        ])->all());

        // Create push data
        $to    = [];
        $title = $task->name . ' | ' . constants('task.status.' . $status) . ' Task';
        $body  = $task->name . ' has been approved by ' . $user->name;
        $task->assignee->push_token ? array_push($to, $task->assignee->push_token) : true;

        // Create notification record to database
        Notification::create([
            'user_id' => $task->assignee_id,
            'title'   => $title,
            'content' => $body,
        ]);

        // Push notification
        $to ? $this->pushToExpo($to, $body, $title) : true;

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

        // Create push data
        $to    = [];
        $title = $task->name . ' | Rejected Task';
        $body  = $task->name . ' has been rejected by ' . $user->name;
        $task->assignee->push_token ? array_push($to, $task->assignee->push_token) : true;

        // Create notification record to database
        Notification::create([
            'user_id' => $task->assignee_id,
            'title'   => $title,
            'content' => $body,
        ]);

        // Push notification
        $to ? $this->pushToExpo($to, $body, $title) : true;

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
        $status = date("Y-m-d H:i:s") >= $request->get('start_at') ? constants('task.status.ongoing') : constants('task.status.not_started');
        $task->update($request->merge([
            'updater_id' => $user->id,
            'status'     => $status,
        ])->all());

        // Create push data
        $to    = [];
        $title = $task->name . ' | ' . constants('task.status.' . $status) . ' Task';
        $body  = $task->name . ' has been updated by ' . $user->name;
        $task->assignee_id == $user->id
            ? ($user->inGroup->manager->push_token ? array_push($to, $user->inGroup->manager->push_token)  : true)
            : ($task->assignee->push_token ? array_push($to, $task->assignee->push_token)  : true);

        // Create notification record to database
        Notification::create([
            'user_id' => $task->assignee_id == $user->id ? $user->inGroup->manager->id : $task->assignee_id,
            'title'   => $title,
            'content' => $body,
        ]);

        // Push notification
        $to ? $this->pushToExpo($to, $body, $title) : true;

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

        // Create push data
        $to    = [];
        $title = $task->name . ' | Committed Task';
        $body  = $task->name . ' has been committed by ' . $user->name;
        $admin = User::where('role', constants('user.role.admin'))->first();

        $user->inGroup->manager->push_token ? array_push($to, $user->inGroup->manager->push_token) : true;
        $admin->push_token ? array_push($to, $admin->push_token) : true;

        // Create notification record to database
        Notification::create([
            'user_id' => $user->inGroup->manager->id,
            'title'   => $title,
            'content' => $body,
        ]);
        Notification::create([
            'user_id' => $admin->id,
            'title'   => $title,
            'content' => $body,
        ]);

        // Push notification
        $to ? $this->pushToExpo($to, $body, $title) : true;

        return response()->json([
            'message' => 'Commit task successfully'
        ]);
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
            'evaluated_at' => date("Y-m-d H:i:s"),
            'updater_id'   => $user->id,
        ])->all());

        // Create push data
        $to    = [];
        $title = $task->name . ' | ' . constants('task.status.' . $request->get('status')) . ' Task';
        $body  = $task->name . ' has been evaluated by ' . $user->name;
        $task->assignee->push_token ? array_push($to, $task->assignee->push_token) : true;

        // Create notification record to database
        Notification::create([
            'user_id' => $task->assignee_id,
            'title'   => $title,
            'content' => $body,
        ]);

        // Push notification
        $to ? $this->pushToExpo($to, $body, $title) : true;

        return response()->json([
            'message' => 'Evaluate task successfully'
        ]);
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
        $user = JWTAuth::parseToken()->authenticate();
        // Create push data
        $to    = [];
        $title = $task->name . ' | Deleted Task';
        $body  = $task->name . ' has been deleted by ' . $user->name;
        $task->assignee->push_token ? array_push($to, $task->assignee->push_token) : true;

        // Create notification record to database
        Notification::create([
            'user_id' => $task->assignee_id,
            'title'   => $title,
            'content' => $body,
        ]);

        // Push notification
        $to ? $this->pushToExpo($to, $body, $title) : true;

        return response()->json([
            'message' => 'Delete task successfully'
        ]);
    }
}
