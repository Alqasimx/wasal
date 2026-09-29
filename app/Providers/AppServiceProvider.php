<?php

namespace App\Providers;

use App\Services\FakeWhatsAppOtpProvider;
use App\Services\WhatsAppOtpProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            WhatsAppOtpProvider::class,
            FakeWhatsAppOtpProvider::class
        );
    }

    public function boot(): void
    {
        //
    }
}