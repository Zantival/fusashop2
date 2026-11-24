<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureKycApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && $user->isMerchant()) {
            if (!$user->companyProfile) {
                return redirect()->route('merchant.profile')->with('info', 'Por favor completa la configuración de tu perfil comercial.');
            }
            if ($user->companyProfile->kyc_status !== 'approved') {
                return redirect()->route('merchant.profile')->with('error', 'Tu cuenta comercial está en proceso de revisión por el Administrador. Una vez validada tu cédula y dirección, podrás comenzar a publicar y vender.');
            }
        }
        return $next($request);
    }
}
