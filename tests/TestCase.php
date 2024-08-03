<?php

namespace MuhammedAlkhudiry\LaravelLongTermTasks\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use MuhammedAlkhudiry\LaravelLongTermTasks\LaravelLongTermTasksServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'MuhammedAlkhudiry\\LaravelLongTermTasks\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelLongTermTasksServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_long_term_tasks_table.php.stub';
        $migration->up();
    }
}
