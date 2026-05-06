<?php

namespace Oh86\LaravelSmBeijingCa;

use Illuminate\Support\ServiceProvider;
use Oh86\LaravelSmBeijingCa\Commands\BeijingCaGenMainKey;
use Oh86\SmCryptor\Facades\Cryptor;

class BeijingCaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Cryptor::extend('beijingCa', function ($app) {
            $config = config('sm_cryptor.beijingCa');
            return new BeijingCaCryptor($config);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                BeijingCaGenMainKey::class,
            ]);
        }
    }
}
