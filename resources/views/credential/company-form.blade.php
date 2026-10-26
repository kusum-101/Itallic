{{-- The company form, shared by Register Your Company (after sign-up), Add New Company and Alter Company.
     $title: the heading, $companyName: the name to start with,
     $company: the company being edited (Alter Company only; then it saves with PUT and has no plan) --}}
@php
  $company = $company ?? null;
  $address = $company?->billingAddress;
@endphp

<form action="{{ $company ? route('company.update', $company->id) : '/company/register' }}" method="POST" class="form-card auth-grid">
  @csrf
  @if($company)
    @method('PUT')
  @endif

  <div class="form-card-head col-full">
    {{ $title }}
    <span class="block text-xs font-normal text-muted-foreground">
      {{ $company ? 'Change the company details below and save.' : 'Create your company account by entering the details below.' }}
    </span>
  </div>

  {{-- Each field: name => [label, type, placeholder, required, value when editing] --}}
  @php
    $fields = [
        'company_name'   => ['Company Name', 'text', 'Enter your company name', true, $company?->company_name],
        'company_email'  => ['Company Email', 'email', 'Enter your company email', true, $company?->company_email],
        'company_mobile' => ['Company Mobile No.', 'tel', 'Enter your company mobile number', true, $company?->company_phone_no],
        'pan_number'     => ['PAN Number', 'text', 'Enter PAN Number', true, $company?->pan],
        'gst_number'     => ['GST Number', 'text', 'e.g. 09AATCM5105A1ZL', false, $company?->gst],
    ];
    $placeFields = [
        'city'     => ['City', 'Enter city name'],
        'state'    => ['State', 'Enter state name'],
        'district' => ['District', 'Enter district name'],
        'pin_code' => ['Pin Code', 'Enter pin code'],
    ];
  @endphp

  @foreach ($fields as $name => [$label, $type, $placeholder, $required, $value])
    <div class="{{ $name == 'company_name' ? 'col-full' : '' }}">
      <label for="{{ $name }}" class="block text-sm font-medium text-foreground">
        {{ $label }} @if($required)<span class="text-destructive-text">*</span>@endif
      </label>
      <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
        value="{{ old($name, $company ? $value : ($name == 'company_name' ? $companyName : '')) }}"
        @if($required) required @endif @if($name == 'company_name') autofocus @endif
        placeholder="{{ $placeholder }}" class="field-input mt-1.5">
      @error($name)
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
      {{-- Sales from other companies on this software reach this company by its GST No. --}}
      @if($name == 'gst_number' && $company)
        <p class="mt-1 text-xs text-muted-foreground">Companies that sell to you on this software find you by this GST No.</p>
      @endif
    </div>
  @endforeach

  <!-- Address section -->
  <div class="auth-section col-full">Address</div>

  <div class="col-full">
    <label for="address" class="block text-sm font-medium text-foreground">Company Address <span class="text-destructive-text">*</span></label>
    <textarea id="address" name="address" rows="3" required placeholder="Enter complete company address"
      class="field-input mt-1.5 py-2">{{ old('address', $address?->address) }}</textarea>
    @error('address')
      <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
    @enderror
  </div>

  @foreach ($placeFields as $name => [$label, $placeholder])
    <div>
      <label for="{{ $name }}" class="block text-sm font-medium text-foreground">{{ $label }} <span class="text-destructive-text">*</span></label>
      <input type="text" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $address?->$name) }}" required
        placeholder="{{ $placeholder }}" class="field-input mt-1.5">
      @error($name)
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>
  @endforeach

  <!-- Plan: only when adding a company -->
  @if(!$company)
  <div class="col-full">
    <label for="plan" class="block text-sm font-medium text-foreground">Select Plan <span class="text-destructive-text">*</span></label>
    <select id="plan" name="plan" required class="field-input mt-1.5">
      <option value="" disabled @selected(!old('plan'))>-- Choose a Plan --</option>
      <option value="free_trial" @selected(old('plan') == 'free_trial')>Free Trial (1 Year / Limited Access) - ₹0/-</option>
      <option value="limited_1year" @selected(old('plan') == 'limited_1year')>Basic Plan - ₹399/-</option>
      <option value="limited_2years" @selected(old('plan') == 'limited_2years')>Advanced Plan - ₹999/-</option>
    </select>
    @error('plan')
      <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
    @enderror
  </div>
  @endif

  <div class="form-card-foot col-full">
    <button type="submit" class="auth-button">{!! $company ? 'Save Changes' : 'Register &amp; Proceed' !!}</button>
    @if($company)
      <a href="{{ route('company-setting') }}" class="auth-button-light text-center">Back</a>
    @else
      <button type="button" onclick="history.back()" class="auth-button-light">Back</button>
    @endif
  </div>
</form>
