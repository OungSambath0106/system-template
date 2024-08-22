<?php

namespace App\Http\Controllers\Backends;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Department;
use App\Models\Grade;
use App\Models\Translation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $grades = Grade::when($request->department_id, function ($query) use ($request) {
            $query->where('department_id', $request->department_id);
        })->orderBy('order', 'asc')
            ->paginate(10);

        $departments = Department::with('compus')->get();

        if ($request->ajax()) {
            $view = view('backends.grade._table', compact('grades', 'departments'))->render();
            return response()->json([
                'view' => $view
            ]);
        }
        return view('backends.grade.index', compact('grades', 'departments'));



        // $grades = Grade::orderBy('order', 'asc')->paginate(10);
        // $departments = Department::with('compus')->get();
        // return view('backends.grade.index', compact('grades', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::with('compus')->get();
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.grade.create', compact('departments', 'language', 'default_lang'));
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
            'department' => 'required',
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

            $grade = new Grade;
            $grade->name = $request->name[array_search('en', $request->lang)];
            $grade->department_id = $request->department;
            //  $grade->order = $request->order;
            $existingItem = Grade::where('order', 1)->first();

            if ($existingItem) {
                // Increment sort_order of all existing items by 1
                Department::where('order', '>=', 1)->increment('order');
            }

            // Set sort_order of the new item to 1
            $grade->order = 1;

            $grade->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Grade',
                        'translationable_id' => $grade->id,
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
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return redirect()->route('admin.grade.index')->with($output);
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
        $grade = Grade::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $departments = Department::with('compus')->get();

        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.grade.edit', compact('grade', 'departments', 'language', 'default_lang'));
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
            'department' => 'required',
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

            $grade = Grade::findOrFail($id);
            $grade->name = $request->name[array_search('en', $request->lang)];
            $grade->department_id = $request->department;
           // $grade->order = $request->order;
           $newSortOrder = $request->input('order');
           // If the new sort order is different from the current one
           if ($grade->order != $newSortOrder) {
               // Increment sort order of items greater than or equal to the new sort order
               Grade::where('order', '>=', $newSortOrder)
                   ->where('id', '!=', $id) // Exclude the current item
                   ->increment('order');

               // Update the sort order of the current item
               $grade->order = $newSortOrder;
            }
            $grade->save();


            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Grade',
                            'translationable_id' => $grade->id,
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

        return redirect()->route('admin.grade.index')->with($output);
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
            $grade = Grade::findOrFail($id);
            $translation = Translation::where('translationable_type', 'App\Models\Grade')
                ->where('translationable_id', $grade->id);
            $translation->delete();
            $grade->delete();

            $grades = Grade::latest('id')->paginate(10);
            $view = view('backends.grade._table', compact('grades'))->render();

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

            $grade = Grade::findOrFail($request->id);
            $grade->status = $grade->status == 1 ? 0 : 1;
            $grade->save();

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
