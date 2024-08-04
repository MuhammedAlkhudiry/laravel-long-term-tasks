<?php

namespace MuhammedAlkhudiry\LaravelLongTermTasks;

use MuhammedAlkhudiry\LaravelLongTermTasks\Commands\ProcessLongTermTasksCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelLongTermTasksServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-long-term-tasks')
            ->hasConfigFile()
            ->hasMigration('create_long_term_tasks_table')
            ->hasCommand(ProcessLongTermTasksCommand::class);
    }
}
