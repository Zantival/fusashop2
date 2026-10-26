<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class MarketingPreferencesController extends Controller
{
    public function show(User $user)
    {
        return view('marketing.unsubscribe', compact('user'));
    }

    public function unsubscribe(Request $request, User $user)
    {
        $request->validate(['confirm' => ['required', 'accepted']]);
        $user->forceFill(['marketing_consent_at' => null])->save();

        return view('marketing.unsubscribed');
    }
}
