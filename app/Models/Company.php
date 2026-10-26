<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Company extends Model
{
    protected $guarded = [];

    /**
     * A GST No.: 2-digit state code, the PAN (5 letters, 4 digits, 1 letter),
     * registration number on that PAN, "Z", check character. e.g. 09AATCM5105A1ZL
     * Used by the company and ledger forms. A sale reaches another company by this number,
     * so a wrong one sends it nowhere (or to the wrong company).
     */
    public const GST_FORMAT = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/';

    public const GST_MESSAGE = 'Enter a valid 15-character GST No., e.g. 09AATCM5105A1ZL.';

    /**
     * Rules of the company form (credential.company-form), for adding a company and for editing one.
     * $company: the company being edited, so its own name, email and GST No. don't count as taken.
     */
    public static function formRules($company = null)
    {
        $unique = fn ($column) => Rule::unique('companies', $column)->ignore($company?->id);

        return [
            'company_name'   => ['required', 'string', 'max:255', $unique('company_name')],
            'company_email'  => ['required', 'email', $unique('company_email')],
            'company_mobile' => 'required|string|max:20',
            'pan_number'     => 'required|string|max:20',
            // Unique: sales from other companies reach this company by its GST No.
            'gst_number'     => ['nullable', 'string', 'regex:' . self::GST_FORMAT, $unique('gst')],

            'address'        => 'required|string',
            'branch_address' => 'nullable|string',
            'city'           => 'required|string|max:100',
            'state'          => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'pin_code'       => 'required|string|max:10',
        ];
    }

    public const FORM_MESSAGES = [
        'gst_number.regex'  => self::GST_MESSAGE,
        'gst_number.unique' => 'Another company already has this GST No.',
    ];

    // The company's billing address (saved with the company form)
    public function billingAddress()
    {
        return $this->hasOne(Address::class)->where('type', 'Billing address');
    }
}
