<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;

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
                'name'                 => 'required|string|unique:tasks|min:6',
                'start_at'             => 'required|date_format:"Y-m-d H:i:s"|after_or_equal:today',
                'end_at'               => 'required|date_format:"Y-m-d H:i:s"|after_or_equal:start_at',
                'description_assigned' => 'required|string',
                'old_task'             => 'integer|exists:tasks,id',
                'assignee_id'          => 'integer|exists:users,id',
            ];
        } else {
            // Update request
            $action = $this->route()->getActionMethod();
            switch ($action) {
                case 'approve':
                    return [
                        'status' => 'required|digits_between:1,2'
                    ];
                break;
                case 'update':
                    return [
                        'name'                 => 'required|string|unique:tasks|min:6',
                        'start_at'             => 'required|date_format:"Y-m-d H:i:s"|after_or_equal:today',
                        'end_at'               => 'required|date_format:"Y-m-d H:i:s"|after_or_equal:start_at',
                        'description_assigned' => 'required|string',
                        'old_task'             => 'integer|exists:tasks,id',
                        'assignee_id'          => 'required|integer|exists:user,id',
                    ];
                break;
                case 'commit':
                    return [
                        'description_committed' => 'required|string'
                    ];
                break;
                case 'evaluate':
                    return [
                        'comment' => 'required|string',
                        'mark'    => 'required',
                        'status'  => 'required|digits_between:6,7',
                    ];
                break;
                default:
                break;
            }
        }
    }
}
