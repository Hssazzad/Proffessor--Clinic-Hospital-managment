<?php

namespace App\Providers;

use App\Repositories\Billing\Contracts\TemporaryBillRepositoryInterface;
use App\Repositories\Billing\TemporaryBillRepository;
use App\Repositories\Billing\Contracts\PaymentListRepositoryInterface;
use App\Repositories\Billing\PaymentListRepository;
use App\Repositories\Billing\Contracts\InvestigationReportRepositoryInterface;
use App\Repositories\Billing\InvestigationReportRepository;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\TemporaryBillService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ====================== Repository Bindings ======================
        $this->app->bind(
            TemporaryBillRepositoryInterface::class,
            TemporaryBillRepository::class
        );

        $this->app->bind(
            PaymentListRepositoryInterface::class,
            PaymentListRepository::class
        );

        $this->app->bind(
            InvestigationReportRepositoryInterface::class,
            InvestigationReportRepository::class
        );

        // InvoiceRepository পরে যোগ করলে এখানে বাইন্ড করবে
        // $this->app->bind(
        //     InvoiceRepositoryInterface::class,
        //     InvoiceRepository::class
        // );

        // ====================== Service Bindings ======================
        $this->app->singleton(InvoiceService::class);
        $this->app->singleton(TemporaryBillService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();
    }
}