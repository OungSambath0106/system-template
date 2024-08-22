<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\Slider;
use App\helpers\AppHelper;
use Illuminate\Http\Request;
use App\helpers\ImageManager;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sliders = Slider::latest('id')->paginate(10);
        return view('backends.slider.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backends.slider._create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'image' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => AppHelper::error_processor($validator)]);
        }

        try {
            DB::beginTransaction();

            $slider = new Slider;
            if ($request->hasFile('image')) {
                $slider->image = ImageManager::upload('uploads/sliders/', $request->image);
            }
            $slider->created_by = auth()->user()->id;
            $slider->save();

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
        $slider = Slider::findOrFail($id);
        return view('backends.slider._edit', compact('slider'));
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
        // $validator = Validator::make($request->all(), [
        //     'image' => 'required|image',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => AppHelper::error_processor($validator)]);
        // }

        try {
            DB::beginTransaction();

            $slider = Slider::findOrFail($id);
            if ($request->hasFile('image')) {
                $slider->image = ImageManager::update('uploads/sliders/', $slider->image, $request->image);
            }
            $slider->save();

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
            $slider = Slider::findOrFail($id);
            $slider->delete();

            if ($slider->image) {
                ImageManager::delete(public_path('uploads/sliders/' . $slider->image));
            }

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

            $slider = Slider::findOrFail($request->id);
            $slider->status = $slider->status == 1 ? 0 : 1;
            $slider->save();

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
        $sliders = Slider::latest('id')->paginate(10);
        $view = view('backends.slider._table', compact('sliders'))->render();

        return ['view' => $view];
    }
}
