<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Console\Command;

class TaskRemind extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind user to do task and commit before due';

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
        // Push notification to remind task is 1 day left to do
        $due_tasks = Task::where('status', constants('task.status.ongoing'))->where('end_at', '<=', date('Y-m-d H:i:s',strtotime("+1 days")))->get();
        foreach ($due_tasks as $task) {
            // Create push data
            $title = $task->name . ' has only 1 day left to do';
            $body  = 'You have to do task and commit it before due';
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
