<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Response;

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
        $group_id = $model->create($request->all())->id;
        User::where('group_id', $group_id)->update(['group_id' => null]);
        User::whereIn('id', $request->get('selected_members'))->update(['group_id' => $group_id]);
        return response()->json([
            'message' => 'Create group successfully'
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
        $group->update($request->all());
        User::where('group_id', $group->id)->update(['group_id' => null]);
        User::whereIn('id', $request->get('selected_members'))->update(['group_id' => $group->id]);
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
