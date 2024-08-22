<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use App\Models\Event;

class SetFrontendSession
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
        $business = new BusinessSetting;

        $copy_right_text = @$business->where('type', 'copy_right_text')->first()->value;
        $web_header_logo = @$business->where('type', 'web_header_logo')->first()->value;
        $web_banner_logo = @$business->where('type', 'web_banner_logo')->first()->value;

        $pending_event = Event::where('start_date', '>=', now())->first();
        $event_id = @$pending_event->id;

        $request->session()->put('copy_right_text', $copy_right_text);
        $request->session()->put('web_header_logo', $web_header_logo);
        $request->session()->put('web_banner_logo', $web_banner_logo);
        $request->session()->put('event_id', $event_id);

        return $next($request);
    }
}
