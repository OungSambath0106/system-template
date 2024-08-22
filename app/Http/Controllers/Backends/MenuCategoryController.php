<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\helpers\AppHelper;
use App\Models\Translation;
use Illuminate\Support\Str;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MenuCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menus = MenuCategory::latest('id')->paginate(10);
        return view('backends.menu.index', compact('menus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parent_menus = MenuCategory::where('parent_id', null)->pluck('name', 'id');
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.menu._create', compact('language', 'default_lang', 'parent_menus'));
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
            'name' => 'required',
        ]);

        if (is_null($request->name[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'name', 'Name field is required!'
                );
            });
        }

        if ($validator->fails()) {
            return response()->json(['errors' => AppHelper::error_processor($validator)]);
        }


        try {
            DB::beginTransaction();

            $menu = new MenuCategory;
            $menu->name = $request->name[array_search('en', $request->lang)];
            $menu->slug = Str::slug($request->name[array_search('en', $request->lang)]);
            if ($request->is_submenu == "on") {
                $menu->parent_id = $request->parent;
                $menu->url = $request->url;
            }
            $menu->created_by = auth()->user()->id;
            $menu->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\MenuCategory',
                        'translationable_id' => $menu->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $request->name[$index],
                    ));
                }
            }
            Translation::insert($data);

            DB::commit();

            $table = $this->renderTable();
            $view = $table['view'];
            $output = [
                'success' => 1,
                'msg' => __('Create successfully'),
                'view' => $view,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong'),
            ];
        }

        return response()->json($output);
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
        $menu = MenuCategory::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $parent_menus = MenuCategory::where('parent_id', null)->pluck('name', 'id');

        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.menu._edit', compact('menu', 'parent_menus', 'language', 'default_lang'));
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
            'name' => 'required',
        ]);

        if (is_null($request->name[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'name', 'Name field is required!'
                );
            });
        }

        if ($validator->fails()) {
            return response()->json(['errors' => AppHelper::error_processor($validator)]);
        }

        try {
            DB::beginTransaction();

            $menu = MenuCategory::findOrFail($id);
            $menu->name = $request->name[array_search('en', $request->lang)];
            $menu->slug = Str::slug($request->name[array_search('en', $request->lang)]);

            $menu->parent_id = null;
            $menu->url = null;
            if ($request->is_submenu == "on") {
                $menu->parent_id = $request->parent;
                $menu->url = $request->url;
            }
            $menu->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\MenuCategory',
                            'translationable_id' => $menu->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $request->name[$index]]
                    );
                }
            }
            Translation::insert($data);

            DB::commit();

            $table = $this->renderTable();
            $view = $table['view'];
            $output = [
                'success' => 1,
                'msg' => __('Updated successfully'),
                'view' => $view,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong'),
            ];
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
            $menu = MenuCategory::findOrFail($id);
            $translation = Translation::where('translationable_type','App\Models\MenuCategory')
                                        ->where('translationable_id',$menu->id);

            $translation->delete();
            $menu->delete();

            $table = $this->renderTable();
            $view = $table['view'];

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

    public function updateStatus (Request $request)
    {
        try {
            DB::beginTransaction();

            $menu = MenuCategory::findOrFail($request->id);
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

    public function renderTable()
    {
        $menus = MenuCategory::latest('id')->paginate(10);
        $view = view('backends.menu._table', compact('menus'))->render();

        return ['view' => $view];
    }
}
