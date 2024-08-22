<?php

namespace App\Http\Controllers\Backends;

use App\Models\Event;
use App\Models\EventDetail;
use Illuminate\Http\Request;
use App\helpers\ImageManager;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $event_details = EventDetail::latest('id')->paginate(10);
        return view('backends.event_detail.index', compact('event_details'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $events = Event::pluck('title', 'id');
        return view('backends.event_detail.create', compact('events'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'event' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with(['success' => 0, 'msg' => __('Invalid form input')]);
        }

        try {
            DB::beginTransaction();

            $event_detail = new EventDetail;
            $event_detail->event_id = $request->event;
            $event_detail->scale = $request->scale;
            $event_detail->visitor = $request->visitor;
            $event_detail->created_by = auth()->user()->id;

            if ($request->filled('image_names')) {
                $event_detail->image = explode(' ', $request->image_names);
            }

            foreach ($event_detail->image as $key => $image) {
                // ImageManager::move_image();
                $image = \File::move(public_path('uploads/temp/' . $image), public_path('uploads/events/'. $image));
            }
            // if ($request->hasFile('image')) {
            //     $event_detail->image = ImageManager::upload('uploads/events/', $request->image);
            // }

            $event_detail->save();

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

        return redirect()->route('admin.event_detail.index')->with($output);
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
        $events = Event::pluck('title', 'id');
        $event_detail = EventDetail::findOrFail($id);
        return view('backends.event_detail.edit', compact('event_detail', 'events'));
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
            'event' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with(['success' => 0, 'msg' => __('Invalid form input')]);
        }

        try {
            DB::beginTransaction();

            $event_detail = EventDetail::findOrFail($id);
            $event_detail->event_id = $request->event;
            $event_detail->scale = $request->scale;
            $event_detail->visitor = $request->visitor;

            if ($request->filled('image_names')) {
                $event_detail->image = explode(' ', $request->image_names);
                foreach ($event_detail->image as $key => $image) {
                    // ImageManager::move_image();
                    $image = \File::move(public_path('uploads/temp/' . $image), public_path('uploads/events/'. $image));
                }
            }

            $event_detail->save();

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('Updated successfully')
            ];
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return redirect()->route('admin.event_detail.index')->with($output);
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
            $event_detail = EventDetail::findOrFail($id);
            $event_detail->delete();

            if ($event_detail->image) {
                foreach ($event_detail->image as $key => $image) {
                    ImageManager::delete(public_path('uploads/events/' . $image));
                }
            }

            $event_details = EventDetail::latest('id')->paginate(10);
            $view = view('backends.event_detail._table', compact('event_details'))->render();

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
