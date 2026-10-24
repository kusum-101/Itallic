<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    // Placeholder for sidebar/header pages that are not built yet.
    public function __invoke(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $title = Str::headline($request->route()->getName());

        return view('pages.coming-soon', compact('user', 'title'));
        
    }
}
