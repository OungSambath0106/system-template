<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpParser\Node\Expr\Isset_;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $packages = Package::latest()->paginate(10);
        return view('backends.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // try{
            $data = $request->validate(
                [
                    'package_id'=>'required|unique:packages',
                    'package_name'=>'required',
                    'package_amount'=>'required|numeric',
                    'referral_commission'=>'required|numeric'
                ],
                [
                    'package_id.required'=>'The Package ID field is required',
                    'package_id.unique'=>'This Package ID has already taken',
                    'package_name.required'=>'The Package Name field is required',
                    'package_amount.required'=>'The Package Amount field is required',
                    'package_amount.numeric'=>'The Package Amount must be number',
                    'referral_commission.required'=>'The Referral Commions field is required',
                    'referral_commission.required'=>'The Referral Commions must be number',
                ]
            );
            $user_id = auth()->user()->id;
            $package = Package::create([
                'package_id'=>$request->package_id,
                'package_name'=>$request->package_name,
                'package_amount'=>$request->package_amount,
                'referral_commission'=>$request->referral_commission,
                'created_by'=>$user_id,
            ]);
            if(!$package){
                $status = 'error';
            }
            $status = 'success';
        // }
        // catch(\Exception $e){
        //     $ouput = $e;
        // }
        if(isset($status)){
            return response()->json(['status'=>$status]);
        }
        // return $ouput;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function show(Package $package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function edit(Package $package)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Package $package)
    {
        // return response($request->id);
        //
        $data = $request->validate(
            [
                'package_id'=>['required', Rule::unique('packages')->ignore($request->id)],
                'package_name'=>'required',
                'package_amount'=>'required|numeric',
                'referral_commission'=>'required|numeric'
            ],
            [
                'package_id.required'=>'The Package ID field is required',
                'package_id.unique'=>'This Package ID has already taken',
                'package_name.required'=>'The Package Name field is required',
                'package_amount.required'=>'The Package Amount field is required',
                'package_amount.numeric'=>'The Package Amount must be number',
                'referral_commission.required'=>'The Referral Commions field is required',
                'referral_commission.required'=>'The Referral Commions must be number',
            ]
        );
        $user_id = auth()->user()->id;
        $package = Package::where('id', $request->id)->update([
            'package_id'=>$request->package_id,
            'package_name'=>$request->package_name,
            'package_amount'=>$request->package_amount,
            'referral_commission'=>$request->referral_commission,
            'created_by'=>$user_id,
        ]);
        if(!$package){
            $status = 'error';
        }
        $status = 'success';
        // }
        // catch(\Exception $e){
        //     $ouput = $e;
        // }
        if(isset($status)){
            return response()->json(['status'=>$status]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function destroy(Package $package)
    {
        $package->delete();
        return response()->json(['status'=>'ok']);
    }
}
