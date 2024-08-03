<?php

namespace MuhammedAlkhudiry\LaravelLongTermTasks;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use MuhammedAlkhudiry\LaravelLongTermTasks\Models\LongTermTask;

class TaskScheduler
{
    protected string $name;

    protected Carbon $scheduledAt;

    protected $thenCallback;

    protected $catchCallback;

    protected $finallyCallback;

    protected bool $shouldQueue = false;

    public function __construct(protected $job = null) {}

    public static function task($job)
    {
        return new self($job);
    }

    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    public function on(Carbon $datetime)
    {
        $this->scheduledAt = $datetime;

        return $this;
    }

    public function then(callable $callback)
    {
        $this->thenCallback = $callback;

        return $this;
    }

    public function catch(callable $callback)
    {
        $this->catchCallback = $callback;

        return $this;
    }

    public function finally(callable $callback)
    {
        $this->finallyCallback = $callback;

        return $this;
    }

    public function serializedThen()
    {
        return $this->thenCallback ? serialize(new SerializableClosure($this->thenCallback)) : null;
    }

    public function serializedCatch()
    {
        return $this->catchCallback ? serialize(new SerializableClosure($this->catchCallback)) : null;
    }

    public function serializedFinally()
    {
        return $this->finallyCallback ? serialize(new SerializableClosure($this->finallyCallback)) : null;
    }

    public function shouldQueue()
    {
        $this->shouldQueue = true;

        return $this;
    }

    public function save()
    {
        /** @var LongTermTask $taskModel */
        $taskModel = config('long-term-tasks.model');

        return $taskModel::create([
            'name' => $this->name ?? (string) Str::uuid(),
            'job' => serialize($this->job),
            'then' => $this->serializedThen(),
            'catch' => $this->serializedCatch(),
            'finally' => $this->serializedFinally(),
            'scheduled_at' => $this->scheduledAt,
            'should_queue' => $this->shouldQueue,
        ]);
    }

    public function update($name)
    {
        $taskModel = config('long-term-tasks.model');

        $task = $taskModel::where('name', $name)->firstOrFail();

        $task->update([
            'job' => serialize($this->job),
            'then' => $this->serializedThen(),
            'catch' => $this->serializedCatch(),
            'finally' => $this->serializedFinally(),
            'scheduled_at' => $this->scheduledAt,
            'should_queue' => $this->shouldQueue,
        ]);
    }

    public static function delete($name)
    {
        $taskModel = config('long-term-tasks.model');
        $task = $taskModel::where('name', $name)->firstOrFail();
        $task->delete();
    }

    public static function getFromModel(LongTermTask $longTermTask): self
    {
        $task = new self(unserialize($longTermTask->job));
        $task->name = $longTermTask->name;
        $task->scheduledAt = $longTermTask->scheduled_at;
        $task->shouldQueue = $longTermTask->should_queue;
        $task->thenCallback = $longTermTask->then ? unserialize($longTermTask->then)->getClosure() : null;
        $task->catchCallback = $longTermTask->catch ? unserialize($longTermTask->catch)->getClosure() : null;
        $task->finallyCallback = $longTermTask->finally ? unserialize($longTermTask->finally)->getClosure() : null;

        return $task;
    }

    public function process()
    {
        try {
            if ($this->shouldQueue) {
                dispatch($this->job);
            } else {
                dispatch_sync($this->job);
            }

            if ($this->thenCallback) {
                call_user_func($this->thenCallback);
            }
        } catch (Exception $e) {
            if ($this->catchCallback) {
                call_user_func($this->catchCallback, $e);
            }
        } finally {
            if ($this->finallyCallback) {
                call_user_func($this->finallyCallback);
            }
        }
    }
}
