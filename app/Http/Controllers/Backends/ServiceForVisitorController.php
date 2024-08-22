<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\helpers\AppHelper;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use App\Models\ServiceForVisitor;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ServiceForVisitorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $services = ServiceForVisitor::orderBy('in_order')->paginate(10);
        return view('backends.service_for_visitor.index', compact('services'));
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

        return view('backends.service_for_visitor._create', compact('language', 'default_lang'));
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

            $service = new ServiceForVisitor;
            $service->name = $request->name[array_search('en', $request->lang)];
            $service->slug = Str::slug($request->name[array_search('en', $request->lang)]);
            $service->url = $request->url;
            $service->in_order = $request->in_order;
            $service->created_by = auth()->user()->id;
            $service->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\ServiceForVisitor',
                        'translationable_id' => $service->id,
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
            dd($e);
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
        $service = ServiceForVisitor::withoutGlobalScopes()->with('translations')->findOrFail($id);

        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.service_for_visitor._edit', compact('service', 'language', 'default_lang'));
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

            $service = ServiceForVisitor::findOrFail($id);
            $service->name = $request->name[array_search('en', $request->lang)];
            $service->slug = Str::slug($request->name[array_search('en', $request->lang)]);
            $service->url = $request->url;
            $service->in_order = $request->in_order;
            $service->created_by = auth()->user()->id;
            $service->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\ServiceForVisitor',
                            'translationable_id' => $service->id,
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
            dd($e);
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
            $service = ServiceForVisitor::findOrFail($id);
            $translation = Translation::where('translationable_type','App\Models\ServiceForVisitor')
                                        ->where('translationable_id',$service->id);

            $translation->delete();
            $service->delete();

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

            $service = ServiceForVisitor::findOrFail($request->id);
            $service->status = $service->status == 1 ? 0 : 1;
            $service->save();

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
        $services = ServiceForVisitor::orderBy('in_order')->paginate(10);
        $view = view('backends.service_for_visitor._table', compact('services'))->render();

        return ['view' => $view];
    }
}
