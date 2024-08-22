<?php

namespace App\Http\Middleware;

use App\Models\BusinessSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetSessionData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('user')) {

            $user = Auth::user();

            $session_data = $user;

            $business = new BusinessSetting;

            $app_icon = @$business->where('type', 'fav_icon')->first()->value;

            $request->session()->put('app_icon', $app_icon);

            $copy_right_text = @$business->where('type', 'copy_right_text')->first()->value;

            $request->session()->put('copy_right_text', $copy_right_text);

            $app_logo = @$business->where('type', 'web_header_logo')->first()->value;

            $request->session()->put('app_logo', $app_logo);

            $app_name = @$business->where('type', 'company_name')->first()->value;

            $request->session()->put('app_name', $app_name);


            // dd($request->session()->all());
            // $request->session()->put('business', $business);
            // $request->session()->put('currency', $currency_data);

        }

        return $next($request);
    }
}
