<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterAdmin;
use Illuminate\Support\Facades\Hash;

class MasterAdminController extends Controller
{
    // 1. Function to check the login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ],[
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        $admin = MasterAdmin::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            
            
            session(['admin_logged_in' => true, 'admin_id' => $admin->id]);

           
            return redirect()->route('dashboard');
        }

        
        return back()->withErrors(['email' => 'email or password is incorrect.'])->withInput();
    }

    public function showDashboard()
    {
   
        if (!session()->has('admin_logged_in')) {
            return redirect('/')->withErrors(['email' => 'Login required to access the dashboard.']);
        }

    
        return view('user-management-html.dashboard');
    }
    public function logout()
    {
        session()->forget(['admin_logged_in', 'admin_id']);
        return redirect('/')->with('success', 'Logged out successfully!');
    }
}
