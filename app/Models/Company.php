<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * A GST No.: 2-digit state code, the PAN (5 letters, 4 digits, 1 letter),
     * registration number on that PAN, "Z", check character. e.g. 09AATCM5105A1ZL
     * Used by the company and ledger forms. A sale reaches another company by this number,
     * so a wrong one sends it nowhere (or to the wrong company).
     */
    public const GST_FORMAT = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/';

    public const GST_MESSAGE = 'Enter a valid 15-character GST No., e.g. 09AATCM5105A1ZL.';
}
