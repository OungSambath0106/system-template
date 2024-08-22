<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\Compus;
use App\Models\Department;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $departments = Department::when($request->compus_id, function ($query) use ($request) {
            $query->where('compus_id', $request->compus_id);
        })->orderBy('order', 'asc')
            ->paginate(10);

        $compuses = Compus::all();

        if ($request->ajax()) {
            $view = view('backends.department._table', compact('departments', 'compuses'))->render();
            return response()->json([
                'view' => $view
            ]);
        }
        return view('backends.department.index', compact('departments', 'compuses'));



        // $departments = Department::orderBy('order', 'asc')->paginate(10);
        // $compuses = Compus::distinct('name')->pluck('name');
        // return view('backends.department.index', compact('departments', 'compuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $compuses = Compus::pluck('name', 'id');
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.department.create', compact('compuses', 'language', 'default_lang'));
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
            'compus' => 'required',
        ]);

        if (is_null($request->name[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'name',
                    'Name field is required!'
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

            $department = new Department;
            $department->name = $request->name[array_search('en', $request->lang)];
            $department->compus_id = $request->compus;
            $department->created_by = auth()->user()->id;
            //$department->order = $request->order;
            $existingItem = Department::where('order', 1)->first();

            if ($existingItem) {
                // Increment sort_order of all existing items by 1
                Department::where('order', '>=', 1)->increment('order');
            }

            // Set sort_order of the new item to 1
            $department->order = 1;

            $department->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Department',
                        'translationable_id' => $department->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $request->name[$index],
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

        return redirect()->route('admin.department.index')->with($output);
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
        $department = Department::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $compuses = Compus::pluck('name', 'id');

        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.department.edit', compact('department', 'compuses', 'language', 'default_lang'));
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
            'compus' => 'required',
        ]);

        if (is_null($request->name[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'name',
                    'Name field is required!'
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

            $department = Department::findOrFail($id);
            $department->name = $request->name[array_search('en', $request->lang)];
            $department->compus_id = $request->compus;
            // $department->order = $request->order;
            $newSortOrder = $request->input('order');
            // If the new sort order is different from the current one
            if ($department->order != $newSortOrder) {
                // Increment sort order of items greater than or equal to the new sort order
                Department::where('order', '>=', $newSortOrder)
                    ->where('id', '!=', $id) // Exclude the current item
                    ->increment('order');

                // Update the sort order of the current item
                $department->order = $newSortOrder;
                $department->save();
            }

            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Department',
                            'translationable_id' => $department->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('Created successfully')
            ];
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return redirect()->route('admin.department.index')->with($output);
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
            $department = Department::findOrFail($id);
            $translation = Translation::where('translationable_type', 'App\Models\Department')
                ->where('translationable_id', $department->id);
            $translation->delete();
            $department->delete();

            $departments = Department::latest('id')->paginate(10);
            $view = view('backends.department._table', compact('departments'))->render();

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

            $department = Department::findOrFail($request->id);
            $department->status = $department->status == 1 ? 0 : 1;
            $department->save();

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
