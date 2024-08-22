<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\Event;
use App\Models\Notice;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class NoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notices = Notice::latest('id')->paginate(10);
        return view('backends.notice.index', compact('notices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $events = Event::pluck('title', 'id');

        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.notice.create', compact('events', 'language', 'default_lang'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event' => 'required',
            'title' => 'required',
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

        try {
            DB::beginTransaction();

            $notice = new Notice;
            $notice->event_id = $request->event;
            $notice->title = $request->title[array_search('en', $request->lang)];
            $notice->content = $request->content[array_search('en', $request->lang)];
            $notice->created_by = auth()->user()->id;

            if ($request->filled('image_names')) {
                $notice->image = explode(' ', $request->image_names);
                foreach ($notice->image as $key => $image) {
                    $directory = public_path('uploads/notices');
                    if (!\File::exists($directory)) {
                        \File::makeDirectory($directory, 0777, true);
                    }

                    $image = \File::move(public_path('uploads/temp/' . $image), public_path('uploads/notices/'. $image));
                }
            }
            if ($request->filled('file_names')) {
                $notice->file = explode(' ', $request->file_names);
                foreach ($notice->file as $key => $image) {
                    $directory = public_path('uploads/notices');
                    if (!\File::exists($directory)) {
                        \File::makeDirectory($directory, 0777, true);
                    }

                    $image = \File::move(public_path('uploads/temp/' . $image), public_path('uploads/notices/'. $image));
                }
            }
            // dd($notice->image);

            $notice->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Notice',
                        'translationable_id' => $notice->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $request->title[$index],
                    ));
                }
                if ($request->content[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Notice',
                        'translationable_id' => $notice->id,
                        'locale' => $key,
                        'key' => 'content',
                        'value' => $request->content[$index],
                    ));
                }
            }
            Translation::insert($data);

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('Created successfully')
            ];
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return redirect()->route('admin.notice.index')->with($output);
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
        $notice = Notice::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $events = Event::pluck('title', 'id');

        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.notice.edit', compact('notice', 'events', 'language', 'default_lang'));
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
        $validator = Validator::make($request->all(), [
            'event' => 'required',
            'title' => 'required',
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

        try {
            DB::beginTransaction();

            $notice = Notice::findOrFail($id);
            $notice->event_id = $request->event;
            $notice->title = $request->title[array_search('en', $request->lang)];
            $notice->content = $request->content[array_search('en', $request->lang)];

            if ($request->filled('image_names')) {
                $notice->image = explode(' ', $request->image_names);
                foreach ($notice->image as $key => $image) {
                    $directory = public_path('uploads/notices');
                    if (!\File::exists($directory)) {
                        \File::makeDirectory($directory, 0777, true);
                    }

                    $image = \File::move(public_path('uploads/temp/' . $image), public_path('uploads/notices/'. $image));
                }
            }
            if ($request->filled('file_names')) {
                $notice->file = explode(' ', $request->file_names);
                foreach ($notice->file as $key => $image) {
                    $directory = public_path('uploads/notices');
                    if (!\File::exists($directory)) {
                        \File::makeDirectory($directory, 0777, true);
                    }

                    $image = \File::move(public_path('uploads/temp/' . $image), public_path('uploads/notices/'. $image));
                }
            }
            // dd($notice->image);

            $notice->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Notice',
                            'translationable_id' => $notice->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $request->title[$index]]
                    );
                }
                if ($request->content[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Notice',
                            'translationable_id' => $notice->id,
                            'locale' => $key,
                            'key' => 'content'],
                        ['value' => $request->content[$index]]
                    );
                }
            }
            Translation::insert($data);

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('Updated successfully')
            ];
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return redirect()->route('admin.notice.index')->with($output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
