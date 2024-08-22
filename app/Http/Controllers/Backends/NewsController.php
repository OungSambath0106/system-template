<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\News;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $news = News::latest('id')->paginate(10);
        return view('backends.news.index', compact('news'));
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
        return view('backends.news.create', compact('language', 'default_lang'));
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
            'title' => 'required',
            'type' => 'required|in:event,news',
            'content' => 'nullable',
        ]);


        if (is_null($request->title[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'title',
                    'title field is required!'
                );
            });
        }
        if (is_null($request->content[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'content',
                    'content field is required!'
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

            $new = new News();
            $new->title = $request->title[array_search('en', $request->lang)];
            $new->type = $request->type;
            $new->content = $request->content[array_search('en', $request->lang)];

            if ($request->filled('thumbnails')) {
                $new->thumbnail = $request->thumbnails;
                $directory = public_path('uploads/News');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0777, true);
                }

                $thumbnail = File::move(public_path('/uploads/temp/' . $request->thumbnails), public_path('uploads/News/' . $request->thumbnails));
            }

            $new->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\News',
                        'translationable_id' => $new->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $request->title[$index],
                    ));
                }
                if ($request->content[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\News',
                        'translationable_id' => $new->id,
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
                'msg' => __('Create successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong'),
            ];
        }
        return redirect()->route('admin.news.index')->with($output);
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
        $new = News::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];
        return view('backends.news.edit', compact('new', 'language', 'default_lang'));
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
            'title' => 'required',
            'type' => 'required|in:event,news',
            'content' => 'nullable',
            'thumbnail' => 'nullable',
        ]);
        if (is_null($request->title[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'title',
                    'title field is required!'
                );
            });
        }
        if (is_null($request->content[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'content',
                    'content field is required!'
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

            $new = News::findOrFail($id);
            $new->title = $request->title[array_search('en', $request->lang)];
            $new->type = $request->type;
            $new->content = $request->content[array_search('en', $request->lang)];
            // if ($request->filled('thumbnails')) {
            //     $new->thumbnail = $request->thumbnails;
            //     $directory = public_path('uploads/News');
            //     if (!File::exists($directory)) {
            //         File::makeDirectory($directory, 0777, true);
            //     }

            //     $thumbnail = File::move(public_path('uploads/temp/' . $request->thumbnails), public_path('uploads/News/'. $request->thumbnails));

            // }

            // // Check if a new image is uploaded
            if ($request->hasFile('thumbnail')) {
                // Delete the old image if it exists
                if ($new->thumbnail) {
                    $oldImagePath = public_path('uploads/News/' . $new->thumbnail);

                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath); // Delete the old image file
                    }
                }

                // Upload and save the new image
                $image = $request->file('thumbnail');

                // Generate a unique filename based on current date and unique identifier
                $imageName = now()->format('Y-m-d') . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

                // Move the uploaded file to the news directory
                $image->move(public_path('uploads/News'), $imageName);

                // Update the thumbnail attribute of the new model
                $new->thumbnail = $imageName;

                // Save the updated new model
                $new->save();
            }
            $new->save();

            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\News',
                            'translationable_id' => $new->id,
                            'locale' => $key,
                            'key' => 'title'
                        ],
                        ['value' => $request->title[$index]]
                    );
                }
            }
            foreach ($request->lang as $index => $key) {
                if (isset($request->content[$index]) && $key != 'en') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\News',
                            'translationable_id' => $new->id,
                            'locale' => $key,
                            'key' => 'content'
                        ],
                        ['value' => $request->content[$index]]
                    );
                }
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('Create successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong'),
            ];
        }
        return redirect()->route('admin.news.index')->with($output);
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
            $new = News::findOrFail($id);
            $translation = Translation::where('translationable_type', 'App\Models\News')
                ->where('translationable_id', $new->id);
            $translation->delete();
            $new->delete();

            $news = News::latest('id')->paginate(10);
            $view = view('backends.news._table', compact('news'))->render();

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
    public function updateStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $new = News::findOrFail($request->id);
            $new->status = $new->status == 1 ? 0 : 1;
            $new->save();

            $output = ['status' => 1, 'msg' => __('Status updated')];

            DB::commit();
        } catch (Exception $e) {

            $output = ['status' => 0, 'msg' => __('Something went wrong')];
            DB::rollBack();
        }

        return response()->json($output);
    }
}
