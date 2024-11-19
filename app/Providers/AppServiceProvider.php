<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Notifications\FcmChannel;
use Illuminate\Notifications\ChannelManager;
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
        $this->app->make(ChannelManager::class)->extend('fcm', function () {
            return new FcmChannel();
        });
    }
}
