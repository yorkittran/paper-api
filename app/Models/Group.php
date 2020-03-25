<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'manager_id',
    ];

    public function members()
    {
        return $this->hasMany('App\Models\User');
    }

    public function manager()
    {
        return $this->belongsTo('App\Models\User', 'manager_id', 'id');
    }
}
