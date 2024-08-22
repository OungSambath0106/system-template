<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\Models\CourseCategory;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CourseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $courses = CourseCategory::latest('id')->paginate(10);
        return view('backends.course-category.index', compact('courses'));
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
        return view('backends.course-category.create', compact('language', 'default_lang'));
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
            'description' => 'nullable',
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

            $course = new CourseCategory();
            $course->title = $request->title[array_search('en', $request->lang)];
            $course->description = $request->description[array_search('en', $request->lang)];

            if ($request->filled('curriculum_images')) {
                $course->curriculum_image = $request->curriculum_images;
                $directory = public_path('uploads/Course');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0777, true);
                }

                $curriculum_image = File::move(public_path('/uploads/temp/' . $request->curriculum_images), public_path('uploads/Course/' . $request->curriculum_images));
            }
            if ($request->filled('assessment_details')) {
                $course->assessment_detail = $request->assessment_details;
                $directory = public_path('uploads/Course');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0777, true);
                }

                $assessment_detail = File::move(public_path('/uploads/temp/' . $request->assessment_details), public_path('uploads/Course/' . $request->assessment_details));
            }
            if ($request->filled('icons')) {
                $course->icon = $request->icons;
                $directory = public_path('uploads/Course');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0777, true);
                }

                $icon = File::move(public_path('/uploads/temp/' . $request->icons), public_path('uploads/Course/' . $request->icons));
            }


            $course->save();


            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\CourseCategory',
                        'translationable_id' => $course->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $request->title[$index],
                    ));
                }
                if ($request->description[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\CourseCategory',
                        'translationable_id' => $course->id,
                        'locale' => $key,
                        'key' => 'description',
                        'value' => $request->description[$index],
                    ));
                }
            }
            // foreach ($request->lang as $index => $key) {
            //     if (isset($request->title[$index]) && $key != 'en') {
            //         array_push($data, array(
            //             'translationable_type' => 'App\Models\CourseCategory',
            //             'translationable_id' => $course->id,
            //             'locale' => $key,
            //             'key' => 'title',
            //             'value' => $request->title[$index],
            //         ));
            //     }
            //     if (isset($request->description[$index]) && $key != 'en') {
            //         array_push($data, array(
            //             'translationable_type' => 'App\Models\CourseCategory',
            //             'translationable_id' => $course->id,
            //             'locale' => $key,
            //             'key' => 'description',
            //             'value' => $request->description[$index],
            //         ));
            //     }
            // }

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
        return redirect()->route('admin.course-category.index')->with($output);
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

        $course = CourseCategory::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];
        return view('backends.course-category.edit', compact('course', 'language', 'default_lang'));
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
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'nullable',
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

            $course =  CourseCategory::findOrFail($id);
            $course->title = $request->title[array_search('en', $request->lang)];
            $course->description = $request->description[array_search('en', $request->lang)];

            // if ($request->filled('curriculum_images')) {
            //     $course->curriculum_image = $request->curriculum_images;
            //     $directory = public_path('uploads/Course');
            //     if (!File::exists($directory)) {
            //         File::makeDirectory($directory, 0777, true);
            //     }

            //     $curriculum_image = File::move(public_path('/uploads/temp/' . $request->curriculum_images), public_path('uploads/Course/' . $request->curriculum_images));
            // }
            // if ($request->filled('assessment_details')) {
            //     $course->assessment_detail = $request->assessment_details;
            //     $directory = public_path('uploads/Course');
            //     if (!File::exists($directory)) {
            //         File::makeDirectory($directory, 0777, true);
            //     }

            //     $assessment_detail = File::move(public_path('/uploads/temp/' . $request->assessment_details), public_path('uploads/Course/' . $request->assessment_details));
            // }
            // if ($request->filled('icons')) {
            //     $course->icon = $request->icons;
            //     $directory = public_path('uploads/Course');
            //     if (!File::exists($directory)) {
            //         File::makeDirectory($directory, 0777, true);
            //     }

            //     $icon = File::move(public_path('/uploads/temp/' . $request->icons), public_path('uploads/Course/' . $request->icons));
            // }
            $this->updateImage($request, $course, 'curriculum_image');
            $this->updateImage($request, $course, 'assessment_detail');
            $this->updateImage($request, $course, 'icon');
            $course->save();

            foreach ($request->lang as $index => $key) {
                if (isset($request->title[$index]) && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\CourseCategory',
                            'translationable_id' => $course->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $request->title[$index]]
                    );
                }
            }
            foreach ($request->lang as $index => $key) {
                if (isset($request->description[$index]) && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\CourseCategory',
                            'translationable_id' => $course->id,
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
        return redirect()->route('admin.course-category.index')->with($output);
    }

    /**
     * Function to handle image update process
     *
     * @param $request
     * @param $course
     * @param $imageFieldName
     */
    function updateImage($request, $course, $imageFieldName)
    {
        // Check if a new image is uploaded
        if ($request->hasFile($imageFieldName)) {
            // Delete the old image if it exists
            if ($course->{$imageFieldName}) {
                $oldImagePath = public_path('uploads/Course/' . $course->{$imageFieldName});

                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image file
                }
            }

            // Upload and save the new image
            $image = $request->file($imageFieldName);

            // Generate a unique filename based on current date and unique identifier
            $imageName = now()->format('Y-m-d') . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Move the uploaded file to the Course directory
            $image->move(public_path('uploads/Course'), $imageName);

            // Update the image attribute of the course model
            $course->{$imageFieldName} = $imageName;

            // Save the updated course model
            $course->save();
        }
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
            $course = CourseCategory::findOrFail($id);
            $translation = Translation::where('translationable_type', 'App\Models\CourseCategory')
                ->where('translationable_id', $course->id);
            $translation->delete();
            $course->delete();

            $courses = CourseCategory::latest('id')->paginate(10);
            $view = view('backends.course-category._table', compact('courses'))->render();

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
