<?php

namespace MuhammedAlkhudiry\LaravelLongTermTasks;

if (! function_exists('schedule')) {
    function schedule($job): TaskScheduler
    {
        return TaskScheduler::task($job);
    }
}
