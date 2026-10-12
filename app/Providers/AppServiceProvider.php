<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Company;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Give every view that shows the company name the current company,
        // fetched fresh from the DB using company_id from session.
        View::composer(['components.sidebar', 'components.header'], function ($view) {
            $view->with('currentCompany', Company::find(session('company_id')));
        });
    }
    
    
}
