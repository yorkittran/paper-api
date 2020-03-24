<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'start_at'       => $this->start_at,
            'end_at'         => $this->end_at,
            'status'         => constants('task.status.' . $this->status),
            'description'    => $this->description,
            'commit_message' => $this->commit_message,
            'attached_file'  => $this->attached_file,
            'comment'        => $this->comment,
            'mark'           => $this->mark,
            'evaluated_at'   => $this->evaluated_at,
            'committed_at'   => $this->committed_at,
            'approved_at'    => $this->approved_at,
            'old_task'       => $this->oldTask ? $this->oldTask->name : null,
            'assigner'       => $this->assigner ? $this->assigner->name : null,
            'assignee'       => $this->assignee ? $this->assignee->name : null,
            'approver'       => $this->approver ? $this->approver->name : null,
            'commenter'      => $this->commenter ? $this->commenter->name : null,
            'creator'        => $this->creator ? $this->creator->name : null,
            'updater'        => $this->updater ? $this->updater->name : null,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
