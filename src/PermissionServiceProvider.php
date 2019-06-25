<?php

namespace Virtualorz\Permission;

use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('permission',function(){
            return new Permission();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->publishes([
            __DIR__.'/config' => base_path('config'),
            __DIR__.'/asset/treeView' => public_path('vendor/treeView'),
        ]);
    }
}
