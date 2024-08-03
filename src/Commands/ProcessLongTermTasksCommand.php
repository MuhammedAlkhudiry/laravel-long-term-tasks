<?php

namespace MuhammedAlkhudiry\LaravelLongTermTasks\Commands;

use Illuminate\Console\Command;
use MuhammedAlkhudiry\LaravelLongTermTasks\Models\LongTermTask;
use MuhammedAlkhudiry\LaravelLongTermTasks\TaskScheduler;

class ProcessLongTermTasksCommand extends Command
{
    protected $signature = 'long-term-tasks:process';

    protected $description = 'Process long-term scheduled tasks';

    public function handle()
    {
        /** @var LongTermTask $longTermTask */
        $longTermTask = config('long-term-tasks.model');

        $longTermTask::whereNull('processed_at')
            ->where('scheduled_at', '<=', now())
            ->each(function ($task) {
                TaskScheduler::getFromModel($task)->process();

                $task->update([
                    'processed_at' => now(),
                ]);
            });
    }
}
