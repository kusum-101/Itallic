<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Address;
use App\Models\Godown;
use App\Models\Group;
use App\Models\Unit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class CredentialController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'email' => 'required|email|string|max:255|unique:users,email',
            'department' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = new User();

        $user->name = $request->name;
        $user->mobile = $request->mobile_no;
        $user->email = $request->email;
        $user->department = $request->department;
        $user->role = $request->role;
        $user->company_name = $request->company_name;
        $user->password = Hash::make($request->password);

        $user->save();

        $request->session()->put('user_id', $user->id);

        return redirect()->route('company');
    }
    public function registerCompany(Request $request)
    {
        // A company always belongs to a logged-in user
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $request->validate([
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'company_email' => 'required|email|unique:companies,company_email',
            'company_mobile' => 'required|string|max:20',
            'pan_number' => 'required|string|max:20',
            'gst_number' => 'nullable|string|max:20',

            'address' => 'required|string',
            'branch_address' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'pin_code' => 'required|string|max:10',

            'plan' => 'required|string',
        ]);

        // Company save
        $company = new Company();

        $company->company_name = $request->company_name;
        $company->company_email = $request->company_email;
        $company->company_phone_no = $request->company_mobile;
        $company->pan = $request->pan_number;
        $company->gst = $request->gst_number;
        $company->user_id = session('user_id');

        $company->save();

        // Give this new company its default set of groups (Tally's standard chart of groups)
        Group::seedDefaults($company->id);

        // ...and its default units (Nos, Kg, ...) and the "Main Location" godown
        Unit::seedDefaults($company->id);
        Godown::seedDefaults($company->id);


        // Address save
        $address = new Address();

        $address->company_id = $company->id;
        $address->company_name = $company->company_name;
        $address->address = $request->address;
        $address->city = $request->city;
        $address->state = $request->state;
        $address->district = $request->district;
        $address->pin_code = $request->pin_code;
        $address->type = 'Billing address';
        $address->status = true;

        $address->save();
        // dd($address->toArray());



        // Count how many companies this user has now
        $totalCompanies = Company::where('user_id', session('user_id'))->count();

        if ($totalCompanies == 1) {
            // This is the user's first company, go straight into it
            session(['company_id' => $company->id]);

            return redirect()->route('dashboardUser');
        }

        // Not the first company, let the user choose which company to stay with
        return redirect()->route('select-company')->with('success', 'Company added. Stay with your current company or switch.');
    }
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {

            // The user_id is saved in the session here
            $request->session()->put('user_id', $user->id);

            $companies = Company::where('user_id', $user->id)->get();

            if ($companies->count() == 0) {
                return redirect()->route('company');
            }

            if ($companies->count() == 1) {
                session(['company_id' => $companies->first()->id]);

                return redirect()->route('dashboardUser');
            }

            return redirect()->route('select-company');
        }

        return back()->with('error', 'Email or Password is incorrect.');
    }

public function company()
{
    $user = User::find(session('user_id'));

    if (!$user) {
        return redirect()->route('login');
    }

    return view('credential.company-register', compact('user'));
}

    public function dashboardUser()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        // If no company is selected yet, send the user to pick one
        if (!session('company_id')) {
            return redirect()->route('select-company');
        }

        return view('user-management.dashboard', compact('user'));
    }

public function logout(Request $request)
{
    // Remove the user and the selected company from the session
$request->session()->invalidate();
$request->session()->regenerateToken();

    return redirect()->route('login');
}


}
