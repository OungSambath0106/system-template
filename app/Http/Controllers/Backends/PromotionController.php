<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\Promotion;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promotions = Promotion::latest('id')->paginate(10);
        return view('backends.promotion.index', compact('promotions'));
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
        return view('backends.promotion.create', compact('language', 'default_lang'));
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
            'short_description' => 'nullable',
            'content' => 'nullable',
            'start_date' => 'nullable',
            'end_date' => 'nullable',
        ]);

        if (is_null($request->title[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'title',
                    'title field is required!'
                );
            });
        }
        if (is_null($request->short_description[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'short_description',
                    'short_description field is required!'
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

            $promotion = new Promotion();
            $promotion->title = $request->title[array_search('en', $request->lang)];
            $promotion->short_description = $request->short_description[array_search('en', $request->lang)];
            $promotion->content = $request->content[array_search('en', $request->lang)];
            $promotion->start_date = $request->start_date;
            $promotion->end_date = $request->end_date;


            if ($request->filled('header_banners')) {
                $promotion->header_banner = $request->header_banners;
                $directory = public_path('uploads/promotions');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0777, true);
                }

                $header_banner = File::move(public_path('/uploads/temp/' . $request->header_banners), public_path('uploads/promotions/' . $request->header_banners));
            }
            if ($request->filled('footer_banners')) {
                $promotion->footer_banner = $request->footer_banners;
                $directory = public_path('uploads/promotions');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0777, true);
                }

                $footer_banner = File::move(public_path('/uploads/temp/' . $request->footer_banners), public_path('uploads/promotions/' . $request->footer_banners));
            }


            $promotion->save();


            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Promotion',
                        'translationable_id' => $promotion->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $request->title[$index],
                    ));
                }
                if ($request->short_description[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Promotion',
                        'translationable_id' => $promotion->id,
                        'locale' => $key,
                        'key' => 'short_description',
                        'value' => $request->short_description[$index],
                    ));
                }
                if ($request->content[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Promotion',
                        'translationable_id' => $promotion->id,
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
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }
        return redirect()->route('admin.promotion.index')->with($output);
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
        $promotion = Promotion::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];
        return view('backends.promotion.edit', compact('promotion', 'language', 'default_lang'));
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
            'short_description' => 'nullable',
            'content' => 'nullable',
            'start_date' => 'nullable',
            'end_date' => 'nullable',
        ]);

        if (is_null($request->title[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'title',
                    'title field is required!'
                );
            });
        }
        if (is_null($request->short_description[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'short_description',
                    'short_description field is required!'
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

            $promotion =  Promotion::findOrFail($id);
            $promotion->title = $request->title[array_search('en', $request->lang)];
            $promotion->short_description = $request->short_description[array_search('en', $request->lang)];
            $promotion->content = $request->content[array_search('en', $request->lang)];
            $promotion->start_date = $request->start_date;
            $promotion->end_date = $request->end_date;

            // Update header banner
            $this->updateImage($request, $promotion, 'header_banner');

            // Update footer banner
            $this->updateImage($request, $promotion, 'footer_banner');
            // // Check if a new image is uploaded
            // if ($request->hasFile('header_banner')) {
            //     // Delete the old image if it exists
            //     if ($promotion->header_banner) {
            //         $oldImagePath = public_path('uploads/promotions/' . $promotion->header_banner);

            //         if (file_exists($oldImagePath)) {
            //             unlink($oldImagePath); // Delete the old image file
            //         }
            //     }

            //     // Upload and save the new image
            //     $image = $request->file('header_banner');

            //     // Generate a unique filename based on current date and unique identifier
            //     $imageName = now()->format('Y-m-d') . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

            //     // Move the uploaded file to the promotions directory
            //     $image->move(public_path('uploads/promotions'), $imageName);

            //     // Update the header_banner attribute of the promotion model
            //     $promotion->header_banner = $imageName;

            //     // Save the updated promotion model
            //     $promotion->save();
            // }

            // // Check if a new image is uploaded
            // if ($request->hasFile('footer_banner')) {
            //     // Delete the old image if it exists
            //     if ($promotion->footer_banner) {
            //         $oldImagePath = public_path('uploads/promotions/' . $promotion->footer_banner);

            //         if (file_exists($oldImagePath)) {
            //             unlink($oldImagePath); // Delete the old image file
            //         }
            //     }

            //     // Upload and save the new image
            //     $image = $request->file('footer_banner');

            //     // Generate a unique filename based on current date and unique identifier
            //     $imageName = now()->format('Y-m-d') . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

            //     // Move the uploaded file to the promotions directory
            //     $image->move(public_path('uploads/promotions'), $imageName);

            //     // Update the footer_banner attribute of the promotion model
            //     $promotion->footer_banner = $imageName;

            //     // Save the updated promotion model
            //     $promotion->save();
            // }
            $promotion->save();

            foreach ($request->lang as $index => $key) {
                if (isset($request->title[$index]) && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Promotion',
                            'translationable_id' => $promotion->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $request->title[$index]]
                    );
                }
            }
            foreach ($request->lang as $index => $key) {
                if (isset($request->short_description[$index]) && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Promotion',
                            'translationable_id' => $promotion->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $request->short_description[$index]]
                    );
                }
            }
            foreach ($request->lang as $index => $key) {
                if (isset($request->content[$index]) && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Promotion',
                            'translationable_id' => $promotion->id,
                            'locale' => $key,
                            'key' => 'content'],
                        ['value' => $request->content[$index]]
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
        return redirect()->route('admin.promotion.index')->with($output);
    }

    /**
     * Function to handle image update process
     *
     * @param $request
     * @param $promotion
     * @param $imageFieldName
     */
    function updateImage($request, $promotion, $imageFieldName)
    {
        // Check if a new image is uploaded
        if ($request->hasFile($imageFieldName)) {
            // Delete the old image if it exists
            if ($promotion->{$imageFieldName}) {
                $oldImagePath = public_path('uploads/promotions/' . $promotion->{$imageFieldName});

                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image file
                }
            }

            // Upload and save the new image
            $image = $request->file($imageFieldName);

            // Generate a unique filename based on current date and unique identifier
            $imageName = now()->format('Y-m-d') . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Move the uploaded file to the promotions directory
            $image->move(public_path('uploads/promotions'), $imageName);

            // Update the image attribute of the promotion model
            $promotion->{$imageFieldName} = $imageName;

            // Save the updated promotion model
            $promotion->save();
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
            $promotion = Promotion::findOrFail($id);
            $translation = Translation::where('translationable_type', 'App\Models\Promotion')
                ->where('translationable_id', $promotion->id);
            $translation->delete();
            $promotion->delete();

            $promotions = Promotion::latest('id')->paginate(10);
            $view = view('backends.promotion._table', compact('promotions'))->render();

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

            $promotion = Promotion::findOrFail($request->id);
            $promotion->status = $promotion->status == 1 ? 0 : 1;
            $promotion->save();

            $output = ['status' => 1, 'msg' => __('Status updated')];

            DB::commit();
        } catch (Exception $e) {
            dd($e);
            $output = ['status' => 0, 'msg' => __('Something went wrong')];
            DB::rollBack();
        }

        return response()->json($output);
    }
}
