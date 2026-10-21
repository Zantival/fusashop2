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
            ->symbols();
    }

    /** Mensajes personalizados que Laravel entrega a la regla Password. */
    public static function messages(): array
    {
        return [
            'password.min'       => 'La contraseña debe tener al menos 12 caracteres.',
            'password.mixed'     => 'La contraseña debe incluir letras mayúsculas y minúsculas.',
            'password.numbers'   => 'La contraseña debe incluir al menos un número.',
            'password.symbols'   => 'La contraseña debe incluir al menos un símbolo.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}
