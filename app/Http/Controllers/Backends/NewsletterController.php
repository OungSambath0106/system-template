<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\Event;
use App\Models\Media;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\helpers\ImageManager;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $newsletters = Media::where('type', 'newsletter')->latest('id')->paginate(10);
        return view('backends.media.newsletter.index', compact('newsletters'));
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

        return view('backends.media.newsletter.create', compact('events', 'language', 'default_lang'));
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

            $newsletter = new Media;
            $newsletter->event_id = $request->event;
            $newsletter->title = $request->title[array_search('en', $request->lang)];
            $newsletter->type = 'newsletter';
            $newsletter->created_by = auth()->user()->id;

            if ($request->filled('image_names')) {
                $newsletter->file = explode(' ', $request->image_names);
                foreach ($newsletter->file as $key => $image) {
                    $directory = public_path('uploads/medias');
                    if (!\File::exists($directory)) {
                        \File::makeDirectory($directory, 0777, true);
                    }

                    $image = \File::move(public_path('uploads/temp/' . $image), public_path('uploads/medias/'. $image));
                }
            }
            // dd($newsletter->image);


            $newsletter->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Media',
                        'translationable_id' => $newsletter->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $request->title[$index],
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

        return redirect()->route('admin.media.newsletter.index')->with($output);

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
        $newsletter = Media::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $events = Event::pluck('title', 'id');

        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.media.newsletter.edit', compact('newsletter', 'events', 'language', 'default_lang'));
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

            $newsletter = Media::findOrFail($id);
            $newsletter->event_id = $request->event;
            $newsletter->title = $request->title[array_search('en', $request->lang)];

            if ($request->filled('image_names')) {
                $newsletter->file = explode(' ', $request->image_names);
                foreach ($newsletter->file as $key => $image) {
                    $directory = public_path('uploads/medias');
                    if (!\File::exists($directory)) {
                        \File::makeDirectory($directory, 0777, true);
                    }

                    $image = \File::move(public_path('uploads/temp/' . $image), public_path('uploads/medias/'. $image));
                }
            }
            // dd($newsletter->image);

            $newsletter->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Media',
                            'translationable_id' => $newsletter->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $request->title[$index]]
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

        return redirect()->route('admin.media.newsletter.index')->with($output);
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
            $newsletter = Media::findOrFail($id);
            $newsletter->delete();

            if ($newsletter->file) {
                foreach ($newsletter->file as $key => $file) {
                    ImageManager::delete(public_path('uploads/medias/' . $file));
                }
            }

            $newsletters = Media::where('type', 'newsletter')->latest('id')->paginate(10);
            $view = view('backends.media.newsletter._table', compact('newsletters'))->render();

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
