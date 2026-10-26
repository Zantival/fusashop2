<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UnrecognizedRegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegistrationSecurityReportController extends Controller
{
    public function show(User $user)
    {
        return view('auth.report-unrecognized-registration', compact('user'));
    }

    public function store(Request $request, User $user)
    {
        $request->validate(['confirm_report' => ['required', 'accepted']]);

        // Bloquear inmediatamente la cuenta y revocar el código OTP para neutralizar cualquier intento de acceso
        $user->update([
            'is_blocked'                   => true,
            'blocked_reason'               => 'Reportado por el titular del correo como registro no autorizado (' . now()->format('d/m/Y H:i') . ')',
            'verification_code'            => null,
            'verification_code_expires_at' => null,
        ]);

        \App\Models\AdminAuditLog::record(
            'security_unrecognized_registration_reported',
            $user,
            'El titular del correo electrónico reportó que no solicitó este registro. Cuenta bloqueada preventivamente.'
        );

        $admins = User::where('role', 'analyst')->get();
        foreach ($admins as $admin) {
            $admin->notify(new UnrecognizedRegistrationNotification($user));
        }

        Log::warning('Unrecognized account registration reported from signed verification email.', [
            'reported_user_id' => $user->id,
            'reported_email'   => $user->email,
            'ip'               => $request->ip(),
            'admin_count'      => $admins->count(),
        ]);

        return view('auth.registration-report-received', compact('user'));
    }
}
