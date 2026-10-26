<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Libera reservas de stock de pedidos sin pago
Illuminate\Support\Facades\Schedule::call(fn () => app(App\Services\OrderPaymentService::class)->expireStaleOrders())
    ->everyFiveMinutes()->name('orders:expire-stale')->withoutOverlapping();

Artisan::command('integrations:status', function () {
    $this->info("================================================================================");
    $this->info(" ESTADO DE INTEGRACIONES (APIs EXTERNAS) - FUSASHOP");
    $this->info("================================================================================");

    $mask = fn(?string $val, int $visible = 6) => empty($val) ? '<fg=red>NO CONFIGURADO</>' : ('<fg=green>' . substr($val, 0, $visible) . '...' . substr($val, -3) . '</>');

    $rows = [
        [
            'Servicio'    => 'Resend (Email)',
            'Ambiente'    => 'Producción / Test',
            'Llave / ID'  => $mask(config('services.resend.key'), 7),
            'Endpoint'    => 'https://api.resend.com/emails',
            'Estado'      => !empty(config('services.resend.key')) ? '<fg=green>ACTIVO</>' : '<fg=yellow>PENDIENTE</>',
        ],
        [
            'Servicio'    => 'Wompi (Pagos)',
            'Ambiente'    => strtoupper((string) config('services.wompi.env', 'sandbox')),
            'Llave / ID'  => $mask(config('services.wompi.public_key'), 8),
            'Endpoint'    => config('services.wompi.api_url'),
            'Estado'      => !empty(config('services.wompi.public_key')) ? '<fg=green>ACTIVO</>' : '<fg=yellow>SANDBOX (Pendiente llaves)</>',
        ],
        [
            'Servicio'    => 'Envia.com (Envíos)',
            'Ambiente'    => strtoupper((string) config('services.envia.env', 'sandbox')),
            'Llave / ID'  => $mask(config('services.envia.token'), 8),
            'Endpoint'    => config('services.envia.api_url'),
            'Estado'      => !empty(config('services.envia.token')) ? '<fg=green>ACTIVO</>' : '<fg=yellow>SANDBOX (Simulado / Pendiente token)</>',
        ],
        [
            'Servicio'    => 'Factus (DIAN Factura)',
            'Ambiente'    => strtoupper((string) config('services.factus.env', 'sandbox')),
            'Llave / ID'  => $mask(config('services.factus.client_id'), 6),
            'Endpoint'    => config('services.factus.api_url'),
            'Estado'      => !empty(config('services.factus.client_id')) ? '<fg=green>ACTIVO</>' : '<fg=yellow>SANDBOX (Simulado / Pendiente OAuth)</>',
        ],
    ];

    $this->table(['Servicio', 'Ambiente', 'Llave / ID', 'Endpoint', 'Estado'], $rows);
    $this->line("");
    $this->comment("Para ingresar o editar tus credenciales, edita el archivo .env");
})->purpose('Muestra el estado de configuración de las APIs de Wompi, Envia, Factus y Resend');

Artisan::command('admin:rotate-password {email=admin@fusashop.com}', function (string $email) {
    $user = User::where('email', strtolower($email))->where('role', 'analyst')->first();
    if (!$user) {
        $this->error('No se encontró una cuenta de administrador con ese correo.');
        return 1;
    }

    $password = $this->secret('Nueva contraseña (mínimo 12 caracteres, mayúscula, minúscula, número y símbolo)');
    $confirmation = $this->secret('Confirma la nueva contraseña');
    if (!is_string($password) || $password === '' || !hash_equals($password, (string) $confirmation)) {
        $this->error('La confirmación no coincide. No se cambió la contraseña.');
        return 1;
    }

    $validation = Validator::make(['password' => $password], ['password' => PasswordPolicy::rule()], PasswordPolicy::messages());
    if ($validation->fails()) {
        $this->error($validation->errors()->first('password'));
        return 1;
    }

    $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
    $user->tokens()->delete();
    $this->info('Contraseña del administrador actualizada. Las sesiones API anteriores fueron revocadas.');
    return 0;
})->purpose('Cambia de forma interactiva y segura la contraseña de una cuenta administradora');

