<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;

class CompanyController extends Controller
{
    // Company Setting page: shows only the logged-in user's company
    public function index()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companies = Company::where('user_id', $user->id)->get();

        return view('company.company-list', compact('user', 'companies'));
    }

    // Select Company page: shows all companies of the logged-in user
    public function select()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companies = Company::where('user_id', $user->id)->get();

        return view('company.select-company', compact('companies'));
    }

    // Save the chosen company in session and go to the dashboard
    public function choose($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        // Find the company by id AND user_id, so another user's company id cannot be opened
        $company = Company::where('id', $id)->where('user_id', $user->id)->first();

        if (!$company) {
            return redirect()->route('select-company');
        }

        session(['company_id' => $company->id]);

        return redirect()->route('dashboardUser');
    }
}
