<?php

namespace App\Providers;

//use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        \App\Models\User::class  => \App\Policies\UserPolicy::class,
        \App\Models\Status::class  => \App\Policies\StatusPolicy::class,
    ];

    public function boot(GateContract $gate)
    {
        //
        Carbon::setlocale('zh');
        $this->registerPolicies($gate);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
