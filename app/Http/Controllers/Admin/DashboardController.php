<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //dashboard
    public function index()
    {
        $users = User::where('id','!=', Auth::user()->id)->get();
        return view('dashboard',compact('users'));
    }
}
