<?php

namespace Digitalup\UpStats;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

class UpStatsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('upstats')
            ->hasViews()
            ->hasRoute('web')
            ->hasMigrations('make_visitors_table', 'make_pages_table', 'make_pagevisits_table')
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishMigrations()
                    ->askToRunMigrations();
            });
    }
}
