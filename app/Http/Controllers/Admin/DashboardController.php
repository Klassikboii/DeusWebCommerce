<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Website;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung-hitungan sederhana untuk dashboard bos
        $totalUsers = User::where('role', 'client')->count();
        $totalWebsites = Website::count();
        
        return view('admin.dashboard', compact('totalUsers', 'totalWebsites'));
    }
}