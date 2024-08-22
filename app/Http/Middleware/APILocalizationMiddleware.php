<?php

namespace App\Http\Middleware;

use Closure;
use App\helpers\AppHelper;
use Illuminate\Support\Facades\App;

class APILocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $local = ($request->hasHeader('lang')) ? (strlen($request->header('lang')) > 0 ? $request->header('lang') : AppHelper::default_lang()) : AppHelper::default_lang();

        if ($local == 'km')
            $local = 'kh';

        App::setLocale($local);

        return $next($request);
    }
}
