<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
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

    // Edit (Alter) Company page: the same form as Add Company, filled with the company and its billing address
    public function edit($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $company = $this->findOwn($id, $user)->load('billingAddress');

        return view('company.edit-company', compact('company'));
    }

    // Save changes to a company and its billing address
    public function update(Request $request, $id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $company = $this->findOwn($id, $user);

        // GST No. is checked and kept in capitals, without spaces around it
        $request->merge(['gst_number' => strtoupper(trim((string) $request->gst_number)) ?: null]);

        $request->validate(Company::formRules($company), Company::FORM_MESSAGES);

        $oldGst = $company->gst;

        DB::transaction(function () use ($request, $company) {
            $company->update([
                'company_name'     => $request->company_name,
                'company_email'    => $request->company_email,
                'company_phone_no' => $request->company_mobile,
                'pan'              => $request->pan_number,
                'gst'              => $request->gst_number,
            ]);

            // A company made before addresses were saved has none yet
            Address::updateOrCreate(
                ['company_id' => $company->id, 'type' => 'Billing address'],
                [
                    'company_name' => $company->company_name,
                    'address'      => $request->address,
                    'city'         => $request->city,
                    'state'        => $request->state,
                    'district'     => $request->district,
                    'pin_code'     => $request->pin_code,
                    'status'       => true,
                ]
            );
        });

        $message = $company->company_name . ' updated successfully.';

        // Sales reach this company by the GST No. on the seller's customer ledger
        if ($oldGst != $company->gst) {
            $message .= ' GST No. changed: companies that sell to you on this software must put the new GST No. on your ledger in their books.';
        }

        return redirect()->route('company-setting')->with('success', $message);
    }

    // One of the logged-in user's companies (404 for another user's company)
    private function findOwn($id, User $user)
    {
        return Company::where('id', $id)->where('user_id', $user->id)->firstOrFail();
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
