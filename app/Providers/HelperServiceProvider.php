<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Helpers/WebhookHelper.php');
    }

    public function boot(): void
    {
        //
    }
}