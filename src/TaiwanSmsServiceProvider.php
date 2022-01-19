<?php
namespace Jarvisho\TaiwanSmsLaravel;

use Illuminate\Support\ServiceProvider;

class TaiwanSmsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/taiwan_sms.php' => config_path('taiwan_sms.php')
        ]);
    }

    public function register()
    {
        $this->app->singleton(TaiwanSms::class, function() {
            return new TaiwanSms();
        });
    }
}
