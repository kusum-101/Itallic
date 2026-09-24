<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="{{ asset('assets/main-BDUTwv5H.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/bootstrap.min.css') }}">
</head>

<body class="min-h-screen flex flex-col bg-light">

  <form class="mx-auto w-full max-w-md" style="margin-block:auto" action="/login-admin" method="POST">
    @csrf

    <div data-wizard-body class="rounded-xl border border-border bg-card">
    
      <fieldset data-step class="p-5">

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label for="email" class="block text-sm font-medium text-foreground">Email</label>
            <input id="email" name="email" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring" />
            @error('email')
                <p class="text-danger text-sm block-element">
                    {{ $message }}
                </p>
            @enderror
            <!-- <p class="mt-1.5 text-xs text-muted-foreground">Put the Registered master admin's email address here.</p> -->
          </div>

          <div class="sm:col-span-2">
            <label for="password" class="block text-sm font-medium text-foreground">Password</label>
            <input id="password" name="password" type="password" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring" />
             @error('password')
                <p class="text-danger text-sm block-element">
                    {{ $message }}
                </p>
            @enderror
          </div>
        </div>
      </fieldset>

      <!-- Controls -->
      <div class="flex items-center justify-between gap-3 border-t border-border p-4">
        <button type="button" data-wizard-back class="rounded-lg border border-input px-4 py-2 text-sm font-medium transition-colors hover:bg-accent disabled:cursor-not-allowed disabled:opacity-40">Back</button>
        <div class="flex gap-2">
          <button
            type="reset"
            class="rounded-lg px-4 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">
            Cancel
          </button>

      
          <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">Submit</button>
        </div>
      </div>
    </div>

  </form>

  @include('components.footer')

</body>

</html>
