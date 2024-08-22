<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\Menu;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menus = Menu::orderBy('sort_order', 'asc')->paginate(10);
        // $menus = Menu::latest('id')->paginate(10);
        return view('backends.app-menu.index', compact('menus'));
    }

    /**
     * Show the form for creating a menu resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];
        return view('backends.app-menu.create', compact('language', 'default_lang'));
    }

    /**
     * Store a menuly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'sort_order' => 'required|integer',
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

            $menu = new Menu();
            $menu->title = $request->title[array_search('en', $request->lang)];
            $menu->description = $request->description[array_search('en', $request->lang)];
            // $menu->sort_order = $request->sort_order;
            // Check if there are existing items with sort_order = 1
            $existingItem = Menu::where('sort_order', 1)->first();

            if ($existingItem) {
                // Increment sort_order of all existing items by 1
                Menu::where('sort_order', '>=', 1)->increment('sort_order');
            }

            // Set sort_order of the new item to 1
            $menu->sort_order = 1;

            if ($request->filled('icons')) {
                $menu->icon = $request->icons;
                $directory = public_path('uploads/menus');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0777, true);
                }

                $icon = File::move(public_path('/uploads/temp/' . $request->icons), public_path('uploads/menus/' . $request->icons));
            }
            if ($request->filled('images')) {
                $menu->image = $request->images;
                $directory = public_path('uploads/menus');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0777, true);
                }

                $image = File::move(public_path('/uploads/temp/' . $request->images), public_path('uploads/menus/' . $request->images));
            }


            $menu->save();


            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Menu',
                        'translationable_id' => $menu->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $request->title[$index],
                    ));
                }
                if ($request->description[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Menu',
                        'translationable_id' => $menu->id,
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
        return redirect()->route('admin.menu.index')->with($output);
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

        $menu = Menu::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];
        return view('backends.app-menu.edit', compact('menu', 'language', 'default_lang'));
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
            'description' => 'required',
            'sort_order' => 'required|integer',
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

            $menu =  Menu::findOrFail($id);
            $menu->title = $request->title[array_search('en', $request->lang)];
            $menu->description = $request->description[array_search('en', $request->lang)];
            // $menu->sort_order = $request->sort_order;
            $newSortOrder = $request->input('sort_order');
            // If the new sort order is different from the current one
            if ($menu->sort_order != $newSortOrder) {
                // Increment sort order of items greater than or equal to the new sort order
                Menu::where('sort_order', '>=', $newSortOrder)
                    ->where('id', '!=', $id) // Exclude the current item
                    ->increment('sort_order');

                // Update the sort order of the current item
                $menu->sort_order = $newSortOrder;
                $menu->save();
            }
            // if ($request->filled('icons')) {
            //     $menu->icon = $request->icons;
            //     $directory = public_path('uploads/menus');
            //     if (!File::exists($directory)) {
            //         File::makeDirectory($directory, 0777, true);
            //     }

            //     $icon = File::move(public_path('/uploads/temp/' . $request->icons), public_path('uploads/menus/' . $request->icons));
            // }
            // Update header banner
            $this->updateImage($request, $menu, 'icon');

            // Update footer banner
            $this->updateImage($request, $menu, 'image');
            $menu->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if (isset($request->title[$index]) && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Menu',
                            'translationable_id' => $menu->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $request->title[$index]]
                    );
                }
            }
            foreach ($request->lang as $index => $key) {
                if (isset($request->description[$index]) && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Menu',
                            'translationable_id' => $menu->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $request->description[$index]]
                    );
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
        return redirect()->route('admin.menu.index')->with($output);
    }


    /**
     * Function to handle image update process
     *
     * @param $request
     * @param $promotion
     * @param $imageFieldName
     */
    function updateImage($request, $menu, $imageFieldName)
    {
        // Check if a new image is uploaded
        if ($request->hasFile($imageFieldName)) {
            // Delete the old image if it exists
            if ($menu->{$imageFieldName}) {
                $oldImagePath = public_path('uploads/menus/' . $menu->{$imageFieldName});

                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image file
                }
            }

            // Upload and save the new image
            $image = $request->file($imageFieldName);

            // Generate a unique filename based on current date and unique identifier
            $imageName = now()->format('Y-m-d') . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Move the uploaded file to the menus directory
            $image->move(public_path('uploads/menus'), $imageName);

            // Update the image attribute of the menu model
            $menu->{$imageFieldName} = $imageName;

            // Save the updated menu model
            $menu->save();
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
            $menu = Menu::findOrFail($id);
            $translation = Translation::where('translationable_type', 'App\Models\Menu')
                ->where('translationable_id', $menu->id);
            $translation->delete();
            $menu->delete();

            $menus = Menu::latest('id')->paginate(10);
            $view = view('backends.app-menu._table', compact('menus'))->render();

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

            $menu = Menu::findOrFail($request->id);
            $menu->status = $menu->status == 1 ? 0 : 1;
            $menu->save();

            $output = ['status' => 1, 'msg' => __('Status updated')];

            DB::commit();
        } catch (Exception $e) {

            $output = ['status' => 0, 'msg' => __('Something went wrong')];
            DB::rollBack();
        }

        return response()->json($output);
    }
}
