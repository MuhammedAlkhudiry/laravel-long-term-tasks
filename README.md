# Laravel Long Term Tasks

[![Latest Version on Packagist](https://img.shields.io/packagist/v/muhammedalkhudiry/laravel-long-term-tasks.svg?style=flat-square)](https://packagist.org/packages/muhammedalkhudiry/laravel-long-term-tasks)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/muhammedalkhudiry/laravel-long-term-tasks/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/muhammedalkhudiry/laravel-long-term-tasks/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/muhammedalkhudiry/laravel-long-term-tasks/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/muhammedalkhudiry/laravel-long-term-tasks/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/muhammedalkhudiry/laravel-long-term-tasks.svg?style=flat-square)](https://packagist.org/packages/muhammedalkhudiry/laravel-long-term-tasks)

This package handles a common cases where you need to run a long term task.
- Example: Delete a user account after 30 days of inactivity

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

## Overview
Let's say you have a client who should have mulitple payments, and we have submit his/her first payment,
you want to remind him/her to submit the second payment after 30 days.

Typically, you would create a command that checks the database for users who have not submitted the second payment and send them a reminder email
and run this command in the schedule.

(and the logic here can be more complex, like checking if the user has a valid subscription, or if the user has a valid payment method, etc.)

```php
// App\Console\Kernel.php
$schedule->command('second-payment-reminder:send')->everyMinute();

// App\Console\Commands\SecondPaymentReminder.php
public function handle()
{
      Payment::query()
        ->where('type', PaymentType::FIRST->value)
        ->where('is_customer_notified', false)
        ->each(
          function (Payment $payment) {
            if ($payment->next_payment_at?->isToday()) {
              $payment->customer->notify(new SecondPaymentReminderNotification($payment));
              $payment->update(['is_customer_notified' => true]);
            }
          }
        );
}
```

using this package, you can create a task that will be executed after 30 days, and you can handle the logic in the task itself.

```php
schedule(new \App\Jobs\SecondPaymentReminder())
    ->on(now()->addDays(30))
    ->name("second-payment-{$payment->id}")
    ->save();
```

And that's it! ✨

Let's say the user refunded the first payment, you can delete the task using the task name.

```php
\MuhammedAlkhudiry\LaravelLongTermTasks\TaskScheduler::delete("second-payment-{$payment->id}");
```

## Usage
### Add the command to your schedule

```php
$schedule->command('long-term-tasks:process')->everyMinute(); // You can change the frequency depending on your needs
```

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
> [!NOTE]
>  `then`, `catch`, and `finally` will be serialized.

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
