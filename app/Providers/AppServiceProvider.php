<?php

namespace App\Providers;

use App\Models\BusinessSetting;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // global variable
        view()->composer('*', function ($view) {
            $business_setting = new BusinessSetting;
            $languages = $business_setting->where('type', 'language')->first()->value;

            $langs = array_reduce(json_decode($languages, true), function ($result, $language) {
                if ($language['status'] == 1) {
                    $result[$language['name']] = $language['code'];
                }
                return $result;

            }, []);

            $view->with('current_locale', app()->getLocale());
            $view->with('available_locales', $langs);
        });
        // view()->composer('*',function($view) {
        //     $view->with('user', Auth::user());
        //     $view->with('social', Social::all());
        //     // if you need to access in controller and views:
        //     Config::set('something', $something);
        // });

        Paginator::useBootstrap();
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

    }
}
