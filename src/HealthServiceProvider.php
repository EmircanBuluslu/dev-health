<?php

namespace DevHealth\LaravelHealth;

use DevHealth\LaravelHealth\Checks\DebugCheck;
use DevHealth\LaravelHealth\Checks\DebugCodeCheck;
use DevHealth\LaravelHealth\Checks\DuplicateRouteCheck;
use DevHealth\LaravelHealth\Checks\EnvSyncCheck;
use DevHealth\LaravelHealth\Checks\MigrationConflictCheck;
use DevHealth\LaravelHealth\Checks\RouteAuthCheck;
use DevHealth\LaravelHealth\Checks\UnusedRouteCheck;
use DevHealth\LaravelHealth\Console\HealthCommand;
use DevHealth\LaravelHealth\Services\DoctorRunner;
use DevHealth\LaravelHealth\Services\ReportFormatter;
use DevHealth\LaravelHealth\Services\RiskCalculator;
use Illuminate\Support\ServiceProvider;

class HealthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Services
        $this->app->singleton(RiskCalculator::class);
        
        $this->app->singleton(ReportFormatter::class, function ($app) {
            return new ReportFormatter($app->make(RiskCalculator::class));
        });

        $this->app->singleton(DoctorRunner::class, function ($app) {
            $runner = new DoctorRunner();
            
            // Tüm kontrolleri kaydet
            $runner->registerChecks([
                new DebugCheck(),
                new DebugCodeCheck(),
                new RouteAuthCheck(),
                new DuplicateRouteCheck(),
                new UnusedRouteCheck(),
                new EnvSyncCheck(),
                new MigrationConflictCheck(),
            ]);

            return $runner;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                HealthCommand::class,
            ]);
        }
    }
}
