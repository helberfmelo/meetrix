<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index()
    {
        $tenants = \App\Models\Tenant::with('user')->latest()->get();
        $users = \App\Models\User::latest()->get();
        
        return view('superadmin.dashboard', compact('tenants', 'users'));
    }
}
