<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if ((bool) config('keycloak.enabled', false)) {
            return redirect()->route('oidc.redirect');
        }

        return view('auth.login');
    }
}
