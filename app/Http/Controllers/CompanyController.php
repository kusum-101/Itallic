<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Support\FinancialYear;
use Illuminate\Validation\Rule;

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

    // Add Company page: the form to add one more company for the logged-in user
    public function create()
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        return view('company.add-company');
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

        // A new company starts in the running financial year
        session(['company_id' => $company->id]);
        session()->forget('financial_year');

        return redirect()->route('dashboardUser');
    }

    // Financial year menu in the header: work in another year (registers, dashboard and new vouchers follow it)
    public function chooseYear(Request $request)
    {
        if (!User::find(session('user_id')) || !session('company_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'year' => ['required', 'integer', Rule::in(FinancialYear::options(session('company_id')))],
        ]);

        session(['financial_year' => (int) $request->year]);

        return redirect()->back()->with('success', 'Financial year changed to ' . FinancialYear::label() . '.');
    }
}
