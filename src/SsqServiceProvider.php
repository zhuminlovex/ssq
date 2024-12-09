<?php

namespace Zm\Ssq;
use Illuminate\Support\ServiceProvider;

class SsqServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 发布配置
        $this->publishes([__DIR__.'/config/ssq.php' => config_path('ssq.php')], 'config');
    }

    /**
     * 如果需要可以绑定单例
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ssq', function ($app) {
            return new Ssq();
        });
    }

}
