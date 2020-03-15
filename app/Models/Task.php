<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'start_at', 'end_at', 'status', 'name', 'description_assigned', 'description_committed',
        'comment', 'mark', 'evaluated_at', 'committed_at', 'approved_at', 'old_task',
        'assigner_id', 'assignee_id', 'approver_id', 'commenter_id', 'updater_id',
    ];

    public function oldTask()
    {
        return $this->belongsTo('App\Models\Task', 'old_task', 'id')->withTrashed();
    }

    public function assigner()
    {
        return $this->belongsTo('App\Models\User', 'assigner_id', 'id')->withTrashed();
    }

    public function assignee()
    {
        return $this->belongsTo('App\Models\User', 'assignee_id', 'id')->withTrashed();
    }

    public function approver()
    {
        return $this->belongsTo('App\Models\User', 'approver_id', 'id')->withTrashed();
    }

    public function commeter()
    {
        return $this->belongsTo('App\Models\User', 'commeter_id', 'id')->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator_id', 'id')->withTrashed();
    }

    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updater_id', 'id')->withTrashed();
    }

}
