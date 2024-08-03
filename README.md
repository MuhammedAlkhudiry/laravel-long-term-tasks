# Laravel Long Term Tasks

[![Latest Version on Packagist](https://img.shields.io/packagist/v/muhammedalkhudiry/laravel-long-term-tasks.svg?style=flat-square)](https://packagist.org/packages/muhammedalkhudiry/laravel-long-term-tasks)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/muhammedalkhudiry/laravel-long-term-tasks/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/muhammedalkhudiry/laravel-long-term-tasks/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/muhammedalkhudiry/laravel-long-term-tasks/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/muhammedalkhudiry/laravel-long-term-tasks/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/muhammedalkhudiry/laravel-long-term-tasks.svg?style=flat-square)](https://packagist.org/packages/muhammedalkhudiry/laravel-long-term-tasks)

This package handles a common cases where you need to run a long term task in your app such as
- Reminding users to complete their profile after 7 days of registration
- When user has multiple payments and whe he/she made the first payment, you want to send him/her a remind for the second payment after 30 days.
- Delete a user account after 30 days of inactivity

## Installation

You can install the package via composer:

```bash
composer require muhammedalkhudiry/laravel-long-term-tasks
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-long-term-tasks-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-long-term-tasks-config"
```

This is the contents of the published config file:

```php
return [
    'model' => \MuhammedAlkhudiry\LaravelLongTermTasks\Models\LongTermTask::class,
];
```

## Usage
### Create a Task
```php
schedule(new \App\Jobs\SecondPaymentReminder())
    ->on(now()->addDays(1)) // Required, the date when the task should be executed
    ->name("second-payment-{$payment->id}") // Optional, you can use it later to delete/update the task
    ->then(function ($task) {
        // When the task is executed
    })
    ->catch(function ($task, $exception) {
        // When the task failed
    })
    ->finally(function ($task) {
        // When the task is executed or failed
    })
    ->shouldQueue() // Optional, by default it will run synchronously
    ->save(); // Required, to save the task

```

### Delete a Task
```php
    \MuhammedAlkhudiry\LaravelLongTermTasks\TaskScheduler::delete("second-payment-{$payment->id}");
```

### Update a Task
```php
\MuhammedAlkhudiry\LaravelLongTermTasks\TaskScheduler::get("second-payment-{$payment->id}")
    ->on(now()->addDays(1))
    ->update();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [muhammed alkhudiry](https://github.com/MuhammedAlkhudiry)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
