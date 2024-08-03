<?php

use MuhammedAlkhudiry\LaravelLongTermTasks\ExampleJob;
use MuhammedAlkhudiry\LaravelLongTermTasks\TaskScheduler;

use function MuhammedAlkhudiry\LaravelLongTermTasks\schedule;
use function Pest\Laravel\travelTo;

it('can schedule a task with a default name', function () {
    $job = new ExampleJob;
    $datetime = now()->addDays(7);

    $task = schedule($job)
        ->on($datetime)
        ->shouldQueue()
        ->save();

    $this->assertDatabaseHas('long_term_tasks', [
        'name' => $task->name,
        'job' => serialize($job),
        'scheduled_at' => $datetime->format('Y-m-d H:i:s'),
        'should_queue' => 1,
    ]);
});

it('can schedule a task with a custom name', function () {
    $job = new ExampleJob;
    $datetime = now()->addDays(7);

    $task = schedule($job)
        ->name('custom-name')
        ->on($datetime)
        ->shouldQueue()
        ->save();

    $this->assertDatabaseHas('long_term_tasks', [
        'name' => 'custom-name',
        'job' => serialize($job),
        'scheduled_at' => $datetime->format('Y-m-d H:i:s'),
        'should_queue' => 1,
    ]);
});

it('can update a task', function () {
    $job = new ExampleJob;
    $datetime = now()->addDays(7);

    $task = schedule($job)
        ->on($datetime)
        ->shouldQueue()
        ->save();

    $task->update([
        'name' => 'new-name',
    ]);

    $this->assertDatabaseHas('long_term_tasks', [
        'name' => 'new-name',
        'job' => serialize($job),
        'scheduled_at' => $datetime->format('Y-m-d H:i:s'),
        'should_queue' => 1,
    ]);
});

it('can delete a task', function () {
    $job = new ExampleJob;
    $datetime = now()->addDays(7);

    $task = schedule($job)
        ->on($datetime)
        ->shouldQueue()
        ->save();

    schedule($job)
        ->on($datetime)
        ->shouldQueue()
        ->save();

    $this->assertDatabaseCount('long_term_tasks', 2);

    TaskScheduler::delete($task->name);

    $this->assertDatabaseCount('long_term_tasks', 1);
});

it('the command can process the tasks', function () {
    $job = new ExampleJob;
    $datetime = now()->addDays(7);

    $task = schedule($job)
        ->on($datetime)
        ->shouldQueue()
        ->save();

    travelTo(now()->addDays(8));

    $this->artisan('long-term-tasks:process')
        ->assertExitCode(0);

    $this->assertDatabaseHas('long_term_tasks', [
        'name' => $task->name,
        'job' => serialize($job),
        'scheduled_at' => $datetime->format('Y-m-d H:i:s'),
        'should_queue' => 1,
        'processed_at' => now()->format('Y-m-d H:i:s'),
    ]);
});

it('the command does not run task before its time', function () {
    $job = new ExampleJob;
    $datetime = now()->addDays(7);

    $task = schedule($job)
        ->on($datetime)
        ->shouldQueue()
        ->save();

    travelTo(now()->addDays(6));

    $this->artisan('long-term-tasks:process')
        ->assertExitCode(0);

    $this->assertDatabaseHas('long_term_tasks', [
        'name' => $task->name,
        'job' => serialize($job),
        'scheduled_at' => $datetime->format('Y-m-d H:i:s'),
        'should_queue' => 1,
        'processed_at' => null,
    ]);
});

it('executes then callback after task runs successfully', function () {
    $job = new ExampleJob;
    $datetime = now()->subMinute();

    $task = schedule($job)
        ->on($datetime)
        ->then(function (TaskScheduler $taskScheduler) {
            // This should be called
            schedule(new ExampleJob)
                ->name('then-callback')
                ->on(now()->addDay())
                ->shouldQueue()
                ->save();
        })
        ->save();

    TaskScheduler::getFromModel($task)->process();

    // Check if the then callback was called
    $this->assertDatabaseHas('long_term_tasks', [
        'name' => 'then-callback',
        'job' => serialize($job),
        'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'should_queue' => 1,
        'processed_at' => null,
    ]);
});

it('executes catch callback after task fails', function () {
    $job = new ExampleJob;
    $datetime = now()->subMinute();

    $task = schedule($job)
        ->on($datetime)
        ->then(function () {
            throw new Exception('This is an exception');
        })
        ->catch(function (TaskScheduler $taskScheduler, Exception $exception) {
            // This should be called
            schedule(new ExampleJob)
                ->on(now()->addDay())
                ->shouldQueue()
                ->save();
        })
        ->save();

    TaskScheduler::getFromModel($task)->process();

    // Check if the catch callback was called
    $this->assertDatabaseHas('long_term_tasks', [
        'name' => $task->name,
        'job' => serialize($job),
        'scheduled_at' => $datetime->format('Y-m-d H:i:s'),
        'should_queue' => 0,
        'processed_at' => null,
    ]);
});

it('executes finally callback after task runs successfully', function () {
    $job = new ExampleJob;
    $datetime = now()->subMinute();

    $task = schedule($job)
        ->on($datetime)
        ->then(function (TaskScheduler $taskScheduler) {
            // This should be called
            schedule(new ExampleJob)
                ->on(now()->addDay())
                ->shouldQueue()
                ->save();
        })
        ->finally(function () {
            // This should be called
            schedule(new ExampleJob)
                ->on(now()->addDay())
                ->shouldQueue()
                ->save();
        })
        ->save();

    TaskScheduler::getFromModel($task)->process();

    // Check if the finally callback was called
    $this->assertDatabaseHas('long_term_tasks', [
        'name' => $task->name,
        'job' => serialize($job),
        'scheduled_at' => $datetime->format('Y-m-d H:i:s'),
        'should_queue' => 0,
        'processed_at' => null,
    ]);
});
