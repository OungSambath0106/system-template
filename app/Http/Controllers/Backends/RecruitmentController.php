<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\Recruitment;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Compus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class RecruitmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $recruitments = Recruitment::with('compus')->get();
        $recruitments = Recruitment::with('compus')->latest('id')->paginate(10);
        return view('backends.recruitment-menu.index', compact('recruitments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $compuses = Compus::all()->pluck('name', 'id');
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];
        return view('backends.recruitment-menu.create', compact('language', 'default_lang','compuses'));
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
            'compus' => 'nullable',
            'description' => 'required',
        ]);

        if (is_null($request->title[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'title',
                    'title field is required!'
                );
            });
        }
        if (is_null($request->description[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'description',
                    'description field is required!'
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

            $recruitment = new Recruitment();
            $recruitment->title = $request->title[array_search('en', $request->lang)];
            $recruitment->description = $request->description[array_search('en', $request->lang)];
            $recruitment->compus_id = $request->compus;


            if ($request->filled('images')) {
                $recruitment->image = $request->images;
                $directory = public_path('uploads/recruitments');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0777, true);
                }

                $image = File::move(public_path('/uploads/temp/' . $request->images), public_path('uploads/recruitments/' . $request->images));
            }


            $recruitment->save();


            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Recruitment',
                        'translationable_id' => $recruitment->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $request->title[$index],
                    ));
                }
                if ($request->description[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Recruitment',
                        'translationable_id' => $recruitment->id,
                        'locale' => $key,
                        'key' => 'description',
                        'value' => $request->description[$index],
                    ));
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
        return redirect()->route('admin.recruitment.index')->with($output);
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
        $compuses = Compus::all()->pluck('name', 'id');
        $recruitment = Recruitment::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];
        return view('backends.recruitment-menu.edit', compact('compuses','recruitment', 'language', 'default_lang'));
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
            'compus' => 'nullable',
            'description' => 'required',
        ]);

        if (is_null($request->title[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'title',
                    'title field is required!'
                );
            });
        }
        if (is_null($request->description[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'description',
                    'description field is required!'
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

            $recruitment =  Recruitment::findOrFail($id);
            $recruitment->title = $request->title[array_search('en', $request->lang)];
            $recruitment->description = $request->description[array_search('en', $request->lang)];
            $recruitment->compus_id = $request->compus;

            // if ($request->filled('images')) {
            //     $recruitment->image = $request->images;
            //     $directory = public_path('uploads/recruitments');
            //     if (!File::exists($directory)) {
            //         File::makeDirectory($directory, 0777, true);
            //     }

            //     $image = File::move(public_path('/uploads/temp/' . $request->images), public_path('uploads/recruitments/' . $request->images));
            // }
            // // Check if a new image is uploaded
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($recruitment->image) {
                    $oldImagePath = public_path('uploads/recruitments/' . $recruitment->image);

                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath); // Delete the old image file
                    }
                }

                // Upload and save the new image
                $image = $request->file('image');

                // Generate a unique filename based on current date and unique identifier
                $imageName = now()->format('Y-m-d') . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

                // Move the uploaded file to the recruitments directory
                $image->move(public_path('uploads/recruitments'), $imageName);

                // Update the image attribute of the recruitment model
                $recruitment->image = $imageName;

                // Save the updated recruitment model
                $recruitment->save();
            }
            $recruitment->save();

            foreach ($request->lang as $index => $key) {
                if (isset($request->title[$index]) && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Recruitment',
                            'translationable_id' => $recruitment->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $request->title[$index]]
                    );
                }
            }
            foreach ($request->lang as $index => $key) {
                if (isset($request->description[$index]) && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Recruitment',
                            'translationable_id' => $recruitment->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $request->description[$index]]
                    );
                }
            }

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

        return redirect()->route('admin.recruitment.index')->with($output);
    }

    public function updateStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $recruitment = Recruitment::findOrFail($request->id);
            $recruitment->status = $recruitment->status == 1 ? 0 : 1;
            $recruitment->save();

            $output = ['status' => 1, 'msg' => __('Status updated')];

            DB::commit();
        } catch (Exception $e) {

            $output = ['status' => 0, 'msg' => __('Something went wrong')];
            DB::rollBack();
        }

        return response()->json($output);
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
            $recruitment = Recruitment::findOrFail($id);
            $translation = Translation::where('translationable_type', 'App\Models\Recruitment')
                ->where('translationable_id', $recruitment->id);
            $translation->delete();
            $recruitment->delete();

            $recruitments = Recruitment::latest('id')->paginate(10);
            $view = view('backends.recruitment-menu._table', compact('recruitments'))->render();

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
