<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (!$this->route()->task) {
            // Create request
            return [
                'name'        => 'required|unique:tasks|min:5',
                'start_at'    => 'required|date_format:"Y-m-d H:i:s"|after_or_equal:today',
                'end_at'      => 'required|date_format:"Y-m-d H:i:s"|after_or_equal:start_at',
                'description' => 'required',
                'old_task'    => 'exists:tasks,id',
            ];
        } else {
            // Update request
            $action = $this->route()->getActionMethod();
            switch ($action) {
                case 'update':
                    return [
                        'name'        => 'required|min:5',
                        'start_at'    => 'required|date_format:"Y-m-d H:i:s"',
                        'end_at'      => 'required|date_format:"Y-m-d H:i:s"|after_or_equal:start_at',
                        'description' => 'required',
                        'old_task'    => 'exists:tasks,id',
                        'assignee_id' => 'required|exists:users,id',
                    ];
                break;
                case 'commit':
                    return [
                        'commit_message' => 'required'
                    ];
                break;
                case 'evaluate':
                    return [
                        'comment' => 'required',
                        'mark'    => 'required',
                        'status'  => 'required',
                    ];
                break;
            }
        }
    }
}
