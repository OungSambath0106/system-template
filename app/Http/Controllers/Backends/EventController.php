<?php

namespace App\Http\Controllers\Backends;

use App\helpers\ImageManager;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::latest('id')->paginate(10);

        return view('backends.event.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        if (request()->ajax()) {
            $key = request('key');
            $tr = view('backends.event._in_kind_support_tr', compact('key'))->render();
            return response()->json([
                'tr' => $tr
            ]);
        }

        return view('backends.event.create', compact('language', 'default_lang'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            // 'event' => 'required',
            // 'partner' => 'required',
        ]);

        if (is_null($request->title[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'title', 'Title field is required!'
                );
            });
        }

        if ($validator->fails()) {
            return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with(['success' => 0, 'msg' => __('Invalid form input')]);
        }

        try{
            $start_date = $request->start_date ? date("Y-m-d", strtotime($request->start_date)) : null;
            $end_date = $request->end_date ? date("Y-m-d", strtotime($request->end_date)) : null;

            DB::beginTransaction();

            $event = new Event;
            $event->title = $request->title[array_search('en', $request->lang)];
            $event->venue = $request->venue[array_search('en', $request->lang)];
            $event->exhibit = $request->exhibit[array_search('en', $request->lang)];
            $event->organizer = $request->organizer[array_search('en', $request->lang)];
            $event->host = $request->host[array_search('en', $request->lang)];
            $event->free_admission = $request->free_admission[array_search('en', $request->lang)];
            $event->how_to_pay = $request->how_to_pay[array_search('en', $request->lang)];

            $event->start_date = $start_date;
            $event->end_date = $end_date;
            $event->video_highlight = $request->video_highlight;
            $event->period = Carbon::parse($end_date)->diffInDays(Carbon::parse($start_date)) + 1;
            $event->operating_hour = date("H:i A", strtotime($request->start_hour)) . ' - ' . date("H:i A", strtotime($request->end_hour));
            $event->scale = json_encode([
                'booth' => $request->booth_scale,
                'company' => $request->compant_scale,
                'visitor' => $request->visitor_scale,
            ]);
            $event->pre_registration_period = $request->pre_registration_period ? date("Y-m-d", strtotime($request->pre_registration_period)) : null;
            $event->application_deadline = $request->application_deadline ? date("Y-m-d", strtotime($request->application_deadline)) : null;
            $event->admission_fee = $request->admission_fee;

            $brochure = [];
            if ($request->hasFile('brochure.thumbnail')) {
                $brochure += [
                    'thumbnail' => ImageManager::upload('uploads/events/', $request->brochure['thumbnail'])
                ];
            }
            if ($request->hasFile('brochure.file')) {
                $brochure += [
                    'file' => ImageManager::upload('uploads/events/', $request->brochure['file'])
                ];
            }
            $event->brochure = json_encode($brochure);

            if ($request->hasFile('offline_application')) {
                $event->offline_application = ImageManager::upload('uploads/events/', $request->offline_application);
            }

            $event->created_by = auth()->user()->id;

            $in_kind_support = [];
            if ($request->in_kind_support) {
                foreach ($request->in_kind_support['title'] as $key => $value) {
                    $item['title'] = $request->in_kind_support['title'][$key];
                    $item['subtitle'] = $request->in_kind_support['subtitle'][$key];
                    $item['description'] = $request->in_kind_support['description'][$key];

                    $request_icon = $request->in_kind_support['icon'] ?? 0;

                    if($request_icon != 0) {
                        if (in_array($key, array_keys($request->in_kind_support['icon']))) {
                            $icon = ImageManager::upload('uploads/events/', $request->in_kind_support['icon'][$key]);
                            $item['icon'] = asset('uploads/events/'.$icon);
                        } else {
                            $item['icon'] = $request->in_kind_support['old_icon'][$key] ?? null;
                        }
                    } else {
                        $item['icon'] = $request->in_kind_support['old_icon'][$key];
                    }

                    array_push($in_kind_support, $item);
                }
                $event->in_kind_support = json_encode($in_kind_support);
            }

            $event->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                $all_input_tran = $request->all();
                foreach ($all_input_tran as $input_name => $input_value) {
                    if (in_array($input_name, ['title', 'venue', 'exhibit', 'organizer', 'host', 'free_admission', 'how_to_pay'])) {
                        if ($request->$input_name[$index] && $key != 'en') {
                            $value = $request->$input_name[$index];
                            array_push($data, array(
                                'translationable_type' => 'App\Models\Event',
                                'translationable_id' => $event->id,
                                'locale' => $key,
                                'key' => $input_name,
                                'value' => $value,
                            ));
                        }
                    }
                }
            }
            Translation::insert($data);

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('Created successfully')
            ];

        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return redirect()->route('admin.event.index')->with($output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $event = Event::withoutGlobalScopes()->with('translations')->findOrFail($id);

        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        if (request()->ajax()) {
            $key = request('key');
            $tr = view('backends.event._in_kind_support_tr', compact('key'))->render();
            return response()->json([
                'tr' => $tr
            ]);
        }

        return view('backends.event.edit', compact('event', 'language', 'default_lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            // 'event' => 'required',
            // 'partner' => 'required',
        ]);

        if (is_null($request->title[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'title', 'Title field is required!'
                );
            });
        }

        if ($validator->fails()) {
            return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with(['success' => 0, 'msg' => __('Invalid form input')]);
        }

        try{
            $start_date = $request->start_date ? date("Y-m-d", strtotime($request->start_date)) : null;
            $end_date = $request->end_date ? date("Y-m-d", strtotime($request->end_date)) : null;

            DB::beginTransaction();

            $event = Event::findOrFail($id);
            $event->title = $request->title[array_search('en', $request->lang)];
            $event->venue = $request->venue[array_search('en', $request->lang)];
            $event->exhibit = $request->exhibit[array_search('en', $request->lang)];
            $event->organizer = $request->organizer[array_search('en', $request->lang)];
            $event->host = $request->host[array_search('en', $request->lang)];
            $event->free_admission = $request->free_admission[array_search('en', $request->lang)];
            $event->how_to_pay = $request->how_to_pay[array_search('en', $request->lang)];

            $event->start_date = $start_date;
            $event->end_date = $end_date;
            $event->video_highlight = $request->video_highlight;
            $event->period = Carbon::parse($end_date)->diffInDays(Carbon::parse($start_date)) + 1;
            $event->operating_hour = date("H:i A", strtotime($request->start_hour)) . ' - ' . date("H:i A", strtotime($request->end_hour));
            $event->scale = json_encode([
                'booth' => $request->booth_scale,
                'company' => $request->compant_scale,
                'visitor' => $request->visitor_scale,
            ]);
            $event->pre_registration_period = $request->pre_registration_period ? date("Y-m-d", strtotime($request->pre_registration_period)) : null;
            $event->application_deadline = $request->application_deadline ? date("Y-m-d", strtotime($request->application_deadline)) : null;
            $event->admission_fee = $request->admission_fee;

            $brochure = [];
            if ($request->hasFile('brochure.thumbnail')) {

                $brochure_data = $event->brochure ? json_decode($event->brochure) : null;
                $brochure += [
                    'thumbnail' => ImageManager::update('uploads/events/', $brochure_data ? $brochure_data->thumbnail : null, $request->brochure['thumbnail'])
                ];
            } else {
                $brochure += [
                    'thumbnail' => $request->old_brochure_thumbnail
                ];
            }
            if ($request->hasFile('brochure.file')) {

                $brochure_data = $event->brochure ? json_decode($event->brochure) : null;
                $brochure += [
                    'file' => ImageManager::update('uploads/events/', $brochure_data ? $brochure_data->file : null, $request->brochure['file'])
                ];
            } else {
                $brochure += [
                    'file' => $request->old_brochure_file
                ];
            }
            $event->brochure = json_encode($brochure);

            if ($request->hasFile('offline_application')) {
                $event->offline_application = ImageManager::update('uploads/events/', $event->offline_application, $request->offline_application);
            }

            $in_kind_support = [];
            if ($request->in_kind_support) {
                foreach ($request->in_kind_support['title'] as $key => $value) {
                    $item['title'] = $request->in_kind_support['title'][$key];
                    $item['subtitle'] = $request->in_kind_support['subtitle'][$key];
                    $item['description'] = $request->in_kind_support['description'][$key];

                    $request_icon = $request->in_kind_support['icon'] ?? 0;

                    if($request_icon != 0) {
                        if (in_array($key, array_keys($request->in_kind_support['icon']))) {
                            $icon = ImageManager::update('uploads/events/', $request->in_kind_support['old_icon'][$key], $request->in_kind_support['icon'][$key]);
                            $item['icon'] = asset('uploads/events/'.$icon);
                        } else {
                            $item['icon'] = $request->in_kind_support['old_icon'][$key] ?? null;
                        }
                    } else {
                        $item['icon'] = $request->in_kind_support['old_icon'][$key];
                    }

                    array_push($in_kind_support, $item);
                }
                $event->in_kind_support = json_encode($in_kind_support);
            }

            $event->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                $all_input_tran = $request->all();
                // dd($all_input_tran);
                foreach ($all_input_tran as $input_name => $input_value) {
                    if (in_array($input_name, ['title', 'venue', 'exhibit', 'organizer', 'host', 'free_admission', 'how_to_pay'])) {

                        if ($request->$input_name[$index] && $key != 'en') {
                            $value = $request->$input_name[$index];

                            Translation::updateOrInsert(
                                ['translationable_type' => 'App\Models\Event',
                                    'translationable_id' => $event->id,
                                    'locale' => $key,
                                    'key' => $input_name],
                                ['value' => $value]
                            );
                        }
                    }
                }
            }

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('Updated successfully')
            ];

        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return redirect()->route('admin.event.index')->with($output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $event = Event::findOrFail($id);
            $translation = Translation::where('translationable_type','App\Models\Event')
                                        ->where('translationable_id',$event->id);
            $translation->delete();
            $event->delete();

            if ($event->brochure) {
                ImageManager::delete(public_path('uploads/events/' . $event->brochure));
            }
            if ($event->offline_application) {
                ImageManager::delete(public_path('uploads/events/' . $event->offline_application));
            }

            $events = Event::latest('id')->paginate(10);
            $view = view('backends.event._table', compact('events'))->render();

            DB::commit();
            $output = [
                'status' => 1,
                'view' => $view,
                'msg' => __('Deleted successfully')
            ];
        } catch (Exception $e) {
            DB::rollBack();
            $output = [
                'status' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return response()->json($output);

    }
}
