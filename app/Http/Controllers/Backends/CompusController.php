<?php

namespace App\Http\Controllers\Backends;

use App\helpers\ImageManager;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Support\Str;
use App\Models\Compus;
use App\Models\Translation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $compuses = Compus::latest('id')->paginate(10);
        return view('backends.compus.index', compact('compuses'));
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

        return view('backends.compus.create', compact('language', 'default_lang'));
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
            'name' => 'required',
            'description' => 'required',
            'address' => 'required',
            'email' => 'required|email|unique:compuses,email',
        ]);

        if (is_null($request->name[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'name',
                    'Name field is required!'
                );
            });
        }
        if (is_null($request->description[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'description',
                    'Description field is required!'
                );
            });
        }
        if (is_null($request->address[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'address',
                    'Address field is required!'
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

            // dd($request->all());
            $compus = new Compus();
            $compus->name = $request->name[array_search('en', $request->lang)];
            $compus->description = $request->description[array_search('en', $request->lang)];
            $compus->address = $request->address[array_search('en', $request->lang)];
            // $compus->phone = $request->phone;
            // $compus->telegram = $request->telegram ?? null;
            // $compus->telegram_url = $request->telegram_url;
            $compus->facebook_name = $request->facebook_name;
            $compus->facebook_url = $request->facebook_url;
            $compus->email = $request->email;
            $compus->google_map_url = $request->google_map_url;

            $phones = [];
            if ($request->phone) {
                foreach ($request->phone as $phone) {
                    $phones[] = $phone;
                }
                $compus->phone = $phones;
            }

            $telegrams = [];
            if ($request->telegrams) {
                foreach ($request->telegrams['telegram_number'] as $key => $number) {
                    $item['telegram_number'] = $number;
                    $item['telegram_url'] = $request->telegrams['telegram_url'][$key];
                    array_push($telegrams, $item);
                }
                $compus->telegram =$telegrams;
            }

            if ($request->hasFile('image')) {
                $compus->image = ImageManager::upload('uploads/compus/', $request->image);
            }

            if ($request->hasFile('admission_image')) {
                $compus->admission_image = ImageManager::upload('uploads/compus/', $request->admission_image);
            }

            $compus->created_by = auth()->user()->id;
            $compus->save();

            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Compus',
                        'translationable_id' => $compus->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $request->name[$index],
                    ));
                }
                if ($request->description[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Compus',
                        'translationable_id' => $compus->id,
                        'locale' => $key,
                        'key' => 'description',
                        'value' => $request->description[$index],
                    ));
                }
                if ($request->address[$index] && $key != 'en') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Compus',
                        'translationable_id' => $compus->id,
                        'locale' => $key,
                        'key' => 'address',
                        'value' => $request->address[$index],
                    ));
                }
            }
            Translation::insert($data);

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('Create successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            // dd($e);
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong'),
            ];
        }

        return redirect()->route('admin.compus.index')->with($output);
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
        $compus = Compus::withoutGlobalScopes()->with('translations')->findOrFail($id);

        $language = BusinessSetting::where('type', 'language')->first();
        $language = $language->value ?? null;
        $default_lang = 'en';
        $default_lang = json_decode($language, true)[0]['code'];

        return view('backends.compus.edit', compact('compus', 'language', 'default_lang'));
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
            'description' => 'required',
            'address' => 'required',
            'email' => 'required|email',
        ]);

        if (is_null($request->name[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'name',
                    'Name field is required!',
                );
            });
        }

        if (is_null($request->description[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'description',
                    'Description field is required!',
                );
            });
        }

        if (is_null($request->address[array_search('en', $request->lang)])) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'address',
                    'Address field is required!',
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

            // dd($request->all());
            $compus = Compus::findOrFail($id);
            $compus->name = $request->name[array_search('en', $request->lang)];
            $compus->description = $request->description[array_search('en', $request->lang)];
            $compus->address = $request->address[array_search('en', $request->lang)];
            // $compus->phone = $request->phone;
            $compus->email = $request->email;
            // $compus->telegram = $request->telegram ?? null;
            // $compus->telegram_url = $request->telegram_url;
            $compus->facebook_name = $request->facebook_name;
            $compus->facebook_url = $request->facebook_url;
            $compus->google_map_url = $request->google_map_url;

            $phones = [];
            if ($request->phone) {
                foreach ($request->phone as $phone) {
                    $phones[] = $phone;
                }
                $compus->phone = $phones;
            }

            $telegrams = [];
            if ($request->telegrams) {
                foreach ($request->telegrams['telegram_number'] as $key => $number) {
                    $item['telegram_number'] = $number;
                    $item['telegram_url'] = $request->telegrams['telegram_url'][$key];
                    array_push($telegrams, $item);
                }
                $compus->telegram =$telegrams;
            }

            if ($request->hasFile('image')) {
                $compus->image = ImageManager::update('uploads/compus/', $compus->image, $request->image);
            }

            if ($request->hasFile('admission_image')) {
                $compus->admission_image = ImageManager::update('uploads/compus/', $compus->admission_image, $request->admission_image);
            }


            $compus->created_by = auth()->user()->id;
            $compus->save();

            foreach ($request->lang as $index => $key) {
                if ($request->name[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Compus',
                            'translationable_id' => $compus->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $request->name[$index]]
                    );
                }
                if ($request->description[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Compus',
                            'translationable_id' => $compus->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $request->description[$index]]
                    );
                }
                if ($request->address[$index] && $key != 'en') {
                    Translation::updateOrInsert(
                        ['translationable_type' => 'App\Models\Compus',
                            'translationable_id' => $compus->id,
                            'locale' => $key,
                            'key' => 'address'],
                        ['value' => $request->address[$index]]
                    );
                }
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('Create successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong'),
            ];
        }

        return redirect()->route('admin.compus.index')->with($output);
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
            $compus = Compus::findOrFail($id);
            $translation = Translation::where('translationable_type', 'App\Models\Compus')
                                        ->where('translationable_id', $compus->id);
            $translation->delete();
            $compus->delete();

            $view = view('backends.compus._table', ['compuses' => Compus::latest()->paginate(10)])->render();

            DB::commit();

            return response()->json([
                'status' => 1,
                'view' => $view,
                'msg' => __('Deleted successfully')
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'msg' => __('Something went wrong')
            ]);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $compus = Compus::findOrFail($request->id);
            $compus->status = $compus->status == 1 ? 0 : 1;
            $compus->save();

            $output = ['status' => 1, 'msg' => __('Status updated')];

            DB::commit();
        } catch (Exception $e) {

            // dd($e);
            $output = ['status' => 0, 'msg' => __('Something went wrong')];
            DB::rollBack();
        }

        return response()->json($output);
    }
}
