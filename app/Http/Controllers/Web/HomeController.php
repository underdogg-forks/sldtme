<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        return redirect()->route('filament.admin.auth.login');
    }
}
