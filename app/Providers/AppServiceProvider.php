<?php

namespace App\Providers;

<<<<<<< HEAD
=======
use App\Models\Donation;
use App\Observers\DonationObserver;
>>>>>>> origin/Donation/Association-"aziz"
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
<<<<<<< HEAD
        //
=======
        Donation::observe(DonationObserver::class);
>>>>>>> origin/Donation/Association-"aziz"
    }
}
