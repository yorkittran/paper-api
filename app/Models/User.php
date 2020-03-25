<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'role', 'password', 'push_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function hasNotifications()
    {
        return $this->hasMany('App\Models\Notification')->orderByDesc('updated_at');
    }

    public function group()
    {
        return $this->hasOne('App\Models\Group', 'manager_id');
    }

    public function inGroup()
    {
        return $this->belongsTo('App\Models\Group', 'group_id', 'id');
    }

    public function givenTask()
    {
        return $this->hasMany('App\Models\Task', 'assignee_id');
    }

    public function handoutTask()
    {
        return $this->hasMany('App\Models\Task', 'assigner_id');
    }

    public function approvedTask()
    {
        return $this->hasMany('App\Models\Task', 'approver_id');
    }
}
