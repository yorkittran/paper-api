<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
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
            return response()->json([
                'data' => User::where('id', '!=', $user->id)->orderBy('role')->orderBy('name')->get(['id', 'name', 'email', 'role'])
            ]);
        } else if ($user->role == constants('user.role.manager')) {
            if (request()->get('include_manager')) {
              return response()->json([
                  'data' => User::where('group_id', $user->group->id)->orWhere('id', $user->id)->orderBy('name')->get(['id', 'name', 'email', 'role'])
              ]);
            } else {
              return response()->json([
                'data' => User::where('group_id', $user->group->id)->orderBy('name')->get(['id', 'name', 'email'])
              ]);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return (new UserResource($user));
    }

    /**
     * Display a listing of the manager not has any group.
     *
     * @return \Illuminate\Http\Response
     */
    public function managers()
    {
        $managers = Group::get('manager_id')->toArray();
        return response()->json([
            'data' => User::where('role', constants('user.role.manager'))->whereNotIn('id', $managers)->get(['id', 'name'])
        ]);
    }

    /**
     * Display a listing of the member not belong any group.
     *
     * @return \Illuminate\Http\Response
     */
    public function members()
    {
        return response()->json([
            'data' => User::where('role', constants('user.role.member'))->where('group_id', null)->get(['id', 'name'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\UserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request, User $model)
    {
        $model->create($request->merge(['password' => Hash::make($request->get('password'))])->all());
        return response()->json([
            'message' => 'Create user successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\UserRequest  $request
     * @param  App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        $user->update($request->merge(['password' => Hash::make($request->get('password'))])->except([$request->get('password') ? '' : 'password']));
        return response()->json([
            'message' => 'Update user successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'message' => 'Delete user successfully'
        ]);
    }
}
