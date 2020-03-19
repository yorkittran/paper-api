<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Response;
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
            return UserResource::collection(User::all())->response()->setStatusCode(Response::HTTP_OK);
        } else if ($user->role == constants('user.role.manager')) {
            return UserResource::collection(User::where('group_id', $user->group->id)->get())->response()->setStatusCode(Response::HTTP_OK);
        }
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
        return response()->json(
            ['message' => 'Create user successfully'],
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_OK);
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
        return response()->json(
            ['message' => 'Update user successfully'],
            Response::HTTP_OK
        );
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
        return response()->json(
            ['message' => 'Delete user successfully'],
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Display a listing of the manager not manage any group.
     *
     * @return \Illuminate\Http\Response
     */
    public function getListOfManagerAvailabled()
    {
        $manager_ids = Group::get('manager_id')->toArray();
        return UserResource::collection(User::where('role', constants('user.role.manager'))->whereNotIn('id', $manager_ids)->get())->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Display a listing of the member not belong any group.
     *
     * @return \Illuminate\Http\Response
     */
    public function getListOfMemberAvailabled()
    {
        return UserResource::collection(User::where('role', constants('user.role.member'))->where('group_id', null)->get())->response()->setStatusCode(Response::HTTP_OK);
    }
}
