<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\helpers\ImageManager;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $users = User::when($request->start_date && $request->end_date, function ($query) use ($request) {
            $query->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date);
        })
            ->latest()
            ->paginate(10);
        return view('backends.user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::select('name', 'id')
            ->pluck('name', 'id');

        return view('backends.user.create', compact('roles'));
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
            'full_name' => 'required',
            // 'last_name' => 'required',
            'username' => 'required',
            // 'user_id' => 'required|unique:users,user_id',
            // 'phone' => 'required',
            'role' => 'required',
            'email' => 'required|email|unique:users,email',
            'dob' => 'nullable',
            'gender' => 'required',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with(['success' => 0, 'msg' => __('Invalid form input')]);
        }

        try {
            DB::beginTransaction();

            $user = new User;
            $user->full_name = $request->full_name;
            // $user->last_name = $request->last_name;
            $user->name = $request->username;
            // $user->user_id = $request->user_id;
            // $user->phone = $request->phone;
            // $user->telegram = $request->telegram ?? null;
            $user->email = $request->email;
            $user->dob = $request->dob;
            $user->gender = $request->gender;
            $user->password = Hash::make($request['password']);

            $role = Role::findOrFail($request->role);
            $user->assignRole($role->name);

            if ($request->hasFile('image')) {
                $user->image = ImageManager::upload('uploads/users/', $request->image);
            }

            $user->save();
            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('Created successfully')
            ];
        } catch (Exception $e) {
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => __('Something went wrong')
            ];
        }

        return redirect()->route('admin.user.index')->with($output);
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
        $user = User::findOrFail($id);
        $roles = Role::select('name', 'id')
            ->pluck('name', 'id');
        return view('backends.user.edit', compact('user', 'roles'));
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

        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            // 'last_name' => 'required',
            'username' => 'required',
            // 'user_id' => 'required|unique:users,user_id,' . $id,
            // 'phone' => 'required',
            'role' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'dob' => 'required',
            'gender' => 'required',
            // 'email' => ['required', 'email', Rule::unique('users')->ignore($id)],
            'password' => 'nullable|min:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with(['success' => 0, 'msg' => __('Invalid form input')]);
        }

        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $user->full_name = $request->full_name;
            // $user->last_name = $request->last_name;
            $user->name = $request->username;
            // $user->user_id = $request->user_id;
            // $user->phone = $request->phone;
            // $user->telegram = $request->telegram ?? null;
            $user->email = $request->email;
            $user->dob = $request->dob;
            $user->gender = $request->gender;

            $role_id        = $request->role;
            $user_role      = $user->roles->first();
            $previous_role  = !empty($user_role->id) ? $user_role->id : 0;
            if ($previous_role != $role_id) {
                if (!empty($previous_role)) {
                    $user->removeRole($user_role->name);
                }

                $role = Role::findOrFail($role_id);
                $user->assignRole($role->name);
            }

            if ($request->password) {
                $user->password = Hash::make($request['password']);
            }
            if ($request->hasFile('image')) {
                $user->image = ImageManager::update('uploads/users/', $user->image, $request->image);
            }

            $user->save();

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

        return redirect()->route('admin.user.index')->with($output);
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
            $user = User::findOrFail($id);
            // if ($user->image) {
            //     ImageManager::delete(public_path('uploads/users/' . $user->image));
            // }
            $user->delete();
            $users = User::latest('id')->paginate(10);
            $view = view('backends.user._table', compact('users'))->render();

            DB::commit();
            $output = [
                'status' => 1,
                'view'  => $view,
                'msg' => __('User Deleted successfully')
            ];
        } catch (Exception $e) {
            DB::rollBack();

            $output = [
                'status' => 0,
                'msg' => __('Something when wrong')
            ];
        }

        return response()->json($output);
    }

    public function updateStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->id);
            $user->status = $user->status == 1 ? 0 : 1;
            $user->save();

            $output = ['status' => 1, 'msg' => __('Status updated')];

            DB::commit();
        } catch (Exception $e) {

            dd($e);
            $output = ['status' => 0, 'msg' => __('Something went wrong')];
            DB::rollBack();
        }

        return response()->json($output);
    }

    // public function filter(Request $request)
    // {
    //     $start_date = $request->start_date;
    //     $end_date = $request->end_date;

    //     $users = User::whereDate('created_at', '>=', $start_date)
    //         ->whereDate('created_at', '<=', $end_date)
    //         ->get();

    //     return view('backends.user.index', compact('users'));
    // }
}
