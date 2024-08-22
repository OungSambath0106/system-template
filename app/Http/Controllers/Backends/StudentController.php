<?php

namespace App\Http\Controllers\Backends;

use App\helpers\ImageManager;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Compus;
use App\Models\Department;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Translation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $students = Student::when($request->department_id, function ($query) use ($request) {
            $query->where('department_id', $request->department_id);
        })
            ->when($request->compus_id, function ($query) use ($request) {
                $query->whereHas('department', function ($q) use ($request) {
                    $q->where('compus_id', $request->compus_id);
                });
            })
            ->when($request->grade_id, function ($query) use ($request) {
                $query->where('grade_id', $request->grade_id);
            })
            ->latest()
            ->paginate(10);

        $departments = Department::with('compus')->get();
        $grades = Grade::all();
        $compuses = Compus::all();

        if ($request->ajax()) {
            $view = view('backends.student._table', compact('students', 'departments', 'grades', 'compuses'))->render();
            return response()->json([
                'view' => $view
            ]);
        }

        return view('backends.student.index', compact('students', 'departments', 'grades', 'compuses'));




        // $students = Student::latest('id')->paginate(10);
        // $departments = Department::with('compus')->get();
        // $grades = Grade::distinct('name')->pluck('name');
        // return view('backends.student.index', compact('students', 'departments', 'grades'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::with('compus')->get();
        $grades = Grade::pluck('name', 'id');
        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.student.create', compact('departments', 'grades', 'language', 'default_lang'));
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
            'department' => 'required',
            'grade' => 'required',
        ]);

        if (is_null($request->title[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'title',
                    'Title field is required!'
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

            $student = new Student;
            $student->title = $request->title[array_search('en', $request->lang)];
            $student->department_id = $request->department;
            $student->grade_id = $request->grade;
            $student->description = $request->description[array_search('en', $request->lang)];

            if ($request->hasFile('image')) {
                $student->image = ImageManager::upload('uploads/studen-report/', $request->image);
            }

            $student->created_by = auth()->user()->id;

            $student->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Student',
                        'translationable_id' => $student->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $request->title[$index],
                    ));
                }
                if ($request->description[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Student',
                        'translationable_id' => $student->id,
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
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return redirect()->route('admin.student.index')->with($output);
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
        $student = Student::withoutGlobalScopes()->with('translations')->findOrFail($id);
        $departments = Department::with('compus')->get();
        $grades = Grade::pluck('name', 'id');

        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.student.edit', compact('student', 'grades', 'departments', 'language', 'default_lang'));
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
            'department' => 'required',
            'grade' => 'required',
        ]);

        if (is_null($request->title[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'title',
                    'Title field is required!'
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

            $student = Student::findOrFail($id);
            $student->title = $request->title[array_search('en', $request->lang)];
            $student->department_id = $request->department;
            $student->grade_id = $request->grade;
            $student->description = $request->description[array_search('en', $request->lang)];

            if ($request->hasFile('image')) {
                $student->image = ImageManager::update('uploads/studen-report/', $student->image, $request->image);
            }

            $student->created_by = auth()->user()->id;

            $student->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->title[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Student',
                            'translationable_id' => $student->id,
                            'locale' => $key,
                            'key' => 'title'
                        ],
                        ['value' => $request->title[$index]]
                    );
                }
                if ($request->description[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Student',
                            'translationable_id' => $student->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
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
        } catch (Exception $e) {
            // dd($e);
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return redirect()->route('admin.student.index')->with($output);
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
            $student = Student::findOrFail($id);
            $translation = Translation::where('translationable_type', 'App\Models\Student')
                ->where('translationable_id', $student->id);
            $translation->delete();
            $student->delete();

            $students = Student::latest('id')->paginate(10);
            $view = view('backends.student._table', compact('students'))->render();

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

            $student = Student::findOrFail($request->id);
            $student->status = $student->status == 1 ? 0 : 1;
            $student->save();

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
