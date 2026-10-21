<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordPolicy
{
    /** Política común para contraseñas nuevas. */
    public static function rule(): Password
    {
        return Password::min(12)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->messages([
                'min' => 'La contraseña debe tener al menos 12 caracteres.',
                'mixed' => 'La contraseña debe incluir letras mayúsculas y minúsculas.',
                'numbers' => 'La contraseña debe incluir al menos un número.',
                'symbols' => 'La contraseña debe incluir al menos un símbolo.',
            ]);
    }
}
