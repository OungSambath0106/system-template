<?php

namespace App\Http\Controllers\Backends;

use App\Http\Controllers\Controller;
use App\Models\Compus;
use App\Models\Recruitment;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $users = User::all();
        $totalusers = count($users);
        $recruitments = Recruitment::all();
        $totalrecruitments = count($recruitments);
        $total_compuses = Compus::count();
        return view('backends.index',compact('totalusers','totalrecruitments','users', 'total_compuses'));
    }
}
