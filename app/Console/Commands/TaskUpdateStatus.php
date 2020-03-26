<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Console\Command;

class TaskUpdateStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status and push notification to user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Ongoing to overdue
        $overdue_tasks = Task::where('status', constants('task.status.ongoing'))->where('end_at', '<=', date('Y-m-d H:i:s'))->get();
        Task::where('status', constants('task.status.ongoing'))->where('end_at', '<=', date('Y-m-d H:i:s'))->update(['status' => constants('task.status.overdue')]);
        foreach ($overdue_tasks as $task) {
            // Create push data
            $title = $task->name . ' | Overdue Task';
            $body  = $task->name . ' has been automatically changed to ongoing';
            // Create notification record to database
            Notification::create([
                'user_id' => $task->assignee_id,
                'title'   => $title,
                'content' => $body,
            ]);
            $task->assignee->push_token ? $this->pushToExpo($task->assignee->push_token, $body, $title) : true;
        }

        // Not started to Ongoing
        $ongoing_tasks = Task::where('status', constants('task.status.not_started'))->where('start_at', '<=', date('Y-m-d H:i:s'))->get();
        Task::where('status', constants('task.status.not_started'))->where('start_at', '<=', date('Y-m-d H:i:s'))->update(['status' => constants('task.status.ongoing')]);
        foreach ($ongoing_tasks as $task) {
            // Create push data
            $title = $task->name . ' | Ongoing Task';
            $body  = $task->name . ' has been automatically changed to ongoing';
            // Create notification record to database
            Notification::create([
                'user_id' => $task->assignee_id,
                'title'   => $title,
                'content' => $body,
            ]);
            $task->assignee->push_token ? $this->pushToExpo($task->assignee->push_token, $body, $title) : true;
        }

        // Pending Approval to Rejected
        $rejected_tasks = Task::where('status', constants('task.status.pending_approval'))->where('end_at', '<=', date('Y-m-d H:i:s'))->get();
        Task::where('status', constants('task.status.pending_approval'))->where('end_at', '<=', date('Y-m-d H:i:s'))->update(['status' => constants('task.status.rejected')]);
        foreach ($rejected_tasks as $task) {
            // Create push data
            $title = $task->name . ' | Rejected Task';
            $body  = $task->name . ' has been automatically changed to rejected';
            // Create notification record to database
            Notification::create([
                'user_id' => $task->assignee_id,
                'title'   => $title,
                'content' => $body,
            ]);
            $task->assignee->push_token ? $this->pushToExpo($task->assignee->push_token, $body, $title) : true;
        }
    }
}
