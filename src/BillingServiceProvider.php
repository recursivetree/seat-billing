<?php

namespace Denngarr\Seat\Billing;

use Denngarr\Seat\Billing\Commands\BillingUpdateLive;
use Denngarr\Seat\Billing\Models\TaxInvoice;
use Denngarr\Seat\Billing\Observers\CorporationWalletJournalObserver;
use Denngarr\Seat\Billing\Observers\TaxInvoiceObserver;
use Seat\Eveapi\Models\Wallet\CorporationWalletJournal;
use Seat\Services\AbstractSeatPlugin;
use Denngarr\Seat\Billing\Commands\BillingUpdate;

class BillingServiceProvider extends AbstractSeatPlugin
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->add_routes();
        $this->add_views();
        $this->add_migrations();
        $this->add_translations();
        $this->add_commands();

        TaxInvoice::observe(TaxInvoiceObserver::class);
        CorporationWalletJournal::observe(CorporationWalletJournalObserver::class);
    }

    /**
     * Include the routes.
     */
    public function add_routes()
    {
        if (! $this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }
    }

    public function add_translations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'billing');
    }

    /**
     * Set the path and namespace for the views.
     */
    public function add_views()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'billing');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__ . '/Config/billing.sidebar.php', 'package.sidebar');

        $this->registerPermissions(
            __DIR__ . '/Config/billing.permissions.php',
            'billing'
        );
    }

    public function add_migrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');
    }

    private function add_commands()
    {
        $this->commands([
            BillingUpdate::class,
            BillingUpdateLive::class
        ]);
    }

    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @example SeAT Web
     *
     * @return string
     */
    public function getName(): string
    {
        return 'SeAT Billing';
    }

    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/recursivetree/seat-billing';
    }

    public function getPackagistPackageName(): string
    {
        return 'seat-billing';
    }

    public function getPackagistVendorName(): string
    {
        return 'recursivetree';
    }
}
