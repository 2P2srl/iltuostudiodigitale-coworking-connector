<?php

declare(strict_types=1);

namespace IlTuoStudioDigitale\CoworkingConnector;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use IlTuoStudioDigitale\CoworkingConnector\Console\InstallCommand;
use IlTuoStudioDigitale\CoworkingConnector\Console\SendOutboxCommand;
use IlTuoStudioDigitale\CoworkingConnector\Events\PlatformEventReceived;
use IlTuoStudioDigitale\CoworkingConnector\Listeners\ReplyToPing;

/**
 * Wires the package into the application: configuration, client, routes, listeners, commands and schedule.
 *
 * @internal
 */
final class ConnectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/platform.php', 'platform');

        $this->app->singleton(ConnectorConfig::class, static fn (): ConnectorConfig => ConnectorConfig::fromArray(
            (array) config('platform', []),
        ));

        $this->app->singleton(PlatformClient::class);
    }

    public function boot(Dispatcher $events): void
    {
        $this->publishes([
            __DIR__.'/../config/platform.php' => config_path('platform.php'),
        ], 'platform-config');

        $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            // With a rolling deploy two containers run the scheduler at once: the lock on the
            // shared cache store lets only one of them send the outbox.
            $schedule->command(SendOutboxCommand::class)->everyFiveMinutes()->withoutOverlapping()->onOneServer();
        });

        if ($this->app->make(ConnectorConfig::class)->replyToPings)
        {
            $events->listen(PlatformEventReceived::class, ReplyToPing::class);
        }

        if ($this->app->runningInConsole())
        {
            $this->commands([InstallCommand::class, SendOutboxCommand::class]);
        }
    }
}
