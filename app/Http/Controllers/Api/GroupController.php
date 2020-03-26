<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\Notification;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return GroupResource::collection(Group::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GroupRequest $request, Group $model)
    {
        $auth_user = JWTAuth::parseToken()->authenticate();
        $group_id = $model->create($request->all())->id;
        User::whereIn('id', $request->get('selected_members'))->update(['group_id' => $group_id]);
        $user_add = User::whereIn('id', $request->get('selected_members'))->get();

        $user_add_token = [];
        $user_add_id    = [];
        foreach ($user_add as $user) {
            $user ? array_push($user_add_token, $user->push_token) : true;
            $user ? array_push($user_add_id, $user->id) : true;
        }

        // Create push data
        $title = 'New notification from system';
        $body  = $auth_user->name . ' has added you to ' . $request->get('name');

        foreach ($user_add_id as $id) {
            // Create notification record to database
            Notification::create([
                'user_id' => $id,
                'title'   => $title,
                'content' => $body,
            ]);
        }

        // Push notification
        $user_add_token ? $this->pushToExpo($to, $body, $title) : true;

        return response()->json([
            'message' => $to
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        return (new GroupResource($group));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Group $group
     * @return \Illuminate\Http\Response
     */
    public function update(GroupRequest $request, Group $group)
    {
        $auth_user = JWTAuth::parseToken()->authenticate();
        $group->update($request->all());
        $user_remove = User::where('group_id', $group->id)->get();
        User::where('group_id', $group->id)->update(['group_id' => null]);
        User::whereIn('id', $request->get('selected_members'))->update(['group_id' => $group->id]);
        $user_add    = User::whereIn('id', $request->get('selected_members'))->get();

        $user_remove_token = [];
        $user_remove_id = [];
        foreach ($user_remove as $user) {
            $user ? array_push($user_remove_token, $user->push_token) : true;
            $user ? array_push($user_remove_id, $user->id) : true;
        }
        $user_add_token = [];
        $user_add_id = [];
        foreach ($user_add as $user) {
            $user ? array_push($user_add_token, $user->push_token) : true;
            $user ? array_push($user_add_id, $user->id) : true;
        }

        $to_add    = array_diff($user_add_token, $user_remove_token);
        $add_id    = array_diff($user_add_id, $user_remove_id);
        $to_remove = array_diff($user_remove_token, $user_add_token);
        $remove_id = array_diff($user_remove_id, $user_add_id);
        // Create push data
        $title_add    = 'New notification from system';
        $body_add     = $auth_user->name . ' has added you to ' . $request->get('name');
        $title_remove = 'New notification from system';
        $body_remove  = $auth_user->name . ' has removed you from ' . $request->get('name');

        foreach ($add_id as $id) {
            // Create notification record to database
            Notification::create([
                'user_id' => $id,
                'title'   => $title_add,
                'content' => $body_add,
            ]);
        }
        foreach ($remove_id as $id) {
            // Create notification record to database
            Notification::create([
                'user_id' => $id,
                'title'   => $title_remove,
                'content' => $body_remove,
            ]);
        }

        // Push notification
        $to_add ? $this->pushToExpo($to_add, $body_add, $title_add) : true;
        $to_remove ? $this->pushToExpo($to_remove, $body_remove, $title_remove) : true;

        return response()->json([
            'message' => 'Update group successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\Group $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        $group->delete();
        return response()->json([
            'message' => 'Delete group successfully'
        ]);
    }
}
