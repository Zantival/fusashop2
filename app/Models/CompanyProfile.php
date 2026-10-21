<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
    protected $hidden = ['wompi_integrity_secret', 'wompi_events_secret'];

    protected $fillable = [
        'merchant_id',
        'company_name',
        'phone',
        'rut_path',
        'camara_comercio_path',
        'logo_path',
        'banners_path',
        'description',
        'address',
        'google_maps_url',
        'latitude',
        'longitude',
        'whatsapp',
        'wompi_public_key',
        'wompi_integrity_secret',
        'wompi_events_secret',
        'payout_method_type',
        'payout_bank_name',
        'payout_account_type',
        'payout_account_number',
        'payout_account_holder',
        'payout_id_number',
        'kyc_status',
        'kyc_rejection_code',
        'kyc_notes',
        'business_type',
        'sector_override',
        'subsector_override',
        'settlement_status',
        'settled_at',
        'employee_count',
    ];

    protected function casts(): array
    {
        return [
            'banners_path' => 'array',
            'settled_at'   => 'datetime',
            'wompi_integrity_secret' => \App\Casts\SafeEncrypted::class,
            'wompi_events_secret' => \App\Casts\SafeEncrypted::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id')->withTrashed();
    }

    public function logoUrl(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }
        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }
        return asset('storage/' . ltrim($this->logo_path, '/'));
    }

    public function hasCustomWompi(): bool
    {
        return !empty($this->wompi_public_key)
            && !empty($this->wompi_integrity_secret)
            && !empty($this->wompi_events_secret);
    }

    public function hasPayoutInfo(): bool
    {
        return !empty($this->payout_account_number);
    }

    public static function availableBanks(): array
    {
        return [
            'Bancolombia',
            'Banco Davivienda',
            'Banco de Bogotá',
            'BBVA Colombia',
            'Banco Agrario de Colombia',
            'Banco de Occidente',
            'Banco Popular',
            'Scotiabank Colpatria',
            'Banco Caja Social',
            'Banco AV Villas',
            'Itaú',
            'Lulo Bank',
            'Nu Colombia',
            'Banco Falabella',
            'Banco Pichincha',
            'Otro Banco Nacional',
        ];
    }

    public static function availableWallets(): array
    {
        return [
            'Nequi',
            'Daviplata',
        ];
    }

    public function resolvedPayoutMethodType(): string
    {
        if (in_array($this->payout_method_type, ['bank', 'wallet'], true)) {
            return $this->payout_method_type;
        }
        if (
            in_array($this->payout_account_type, ['Nequi', 'Daviplata'], true)
            || in_array($this->payout_bank_name, ['Nequi', 'Daviplata'], true)
        ) {
            return 'wallet';
        }
        return 'bank';
    }

    public function formattedPayoutSummary(): string
    {
        if (!$this->hasPayoutInfo()) {
            return 'Sin método de pago configurado';
        }

        if ($this->resolvedPayoutMethodType() === 'wallet') {
            $wallet = in_array($this->payout_bank_name, ['Nequi', 'Daviplata'], true)
                ? $this->payout_bank_name
                : ($this->payout_account_type ?: 'Billetera Digital');
            return "Billetera Digital {$wallet} • Cel: {$this->payout_account_number}";
        }

        $bank = $this->payout_bank_name ?: 'Banco';
        $type = $this->payout_account_type ?: 'Ahorros';
        return "Cuenta {$type} {$bank} • Nº {$this->payout_account_number}";
    }

    /**
     * Valida y normaliza la configuración de pago preferido del comerciante
     * (Cuenta Bancaria Tradicional o Billetera Digital Nequi / Daviplata).
     */
    public static function validateAndExtractPayoutData(\Illuminate\Http\Request $request, bool $requireMethod = false): array
    {
        $rawMethod = $request->input('payout_method_type');
        $rawAccType = $request->input('payout_account_type');
        $rawBank = $request->input('payout_bank_name');

        if (!$rawMethod) {
            if (in_array($rawAccType, ['Nequi', 'Daviplata'], true) || in_array($rawBank, ['Nequi', 'Daviplata'], true) || $request->filled('wallet_phone')) {
                $rawMethod = 'wallet';
            } elseif ($request->filled('payout_account_number') || $request->filled('payout_bank_name')) {
                $rawMethod = 'bank';
            }
        }

        $hasAnyInput = !empty($rawMethod)
            || $request->filled('payout_account_number')
            || $request->filled('wallet_phone')
            || $request->filled('payout_bank_name');

        if (!$requireMethod && !$hasAnyInput) {
            return [];
        }

        $method = $rawMethod === 'wallet' ? 'wallet' : 'bank';

        if ($method === 'wallet') {
            $request->merge([
                'wallet_provider' => $request->input('wallet_provider') ?: (in_array($rawBank, ['Nequi', 'Daviplata'], true) ? $rawBank : ($rawAccType ?: 'Nequi')),
                'wallet_phone'    => preg_replace('/\D+/', '', (string) ($request->input('wallet_phone') ?: $request->input('payout_account_number'))),
            ]);

            $request->validate([
                'wallet_provider'       => ['required', 'string', 'in:Nequi,Daviplata'],
                'wallet_phone'          => ['required', 'string', 'regex:/^[0-9]{10}$/'],
                'payout_account_holder' => ['nullable', 'string', 'max:150'],
                'payout_id_number'      => ['nullable', 'string', 'max:40'],
            ], [
                'wallet_provider.required' => 'Selecciona la billetera digital (Nequi o Daviplata).',
                'wallet_provider.in'       => 'La billetera digital debe ser Nequi o Daviplata.',
                'wallet_phone.required'    => 'Ingresa el número de teléfono celular asociado a tu billetera digital.',
                'wallet_phone.regex'       => 'El número de teléfono de Nequi o Daviplata debe tener exactamente 10 dígitos numéricos.',
            ]);

            $provider = strip_tags(trim((string) $request->input('wallet_provider')));
            $phone    = preg_replace('/\D+/', '', (string) $request->input('wallet_phone'));

            return array_filter([
                'payout_method_type'    => 'wallet',
                'payout_bank_name'      => $provider,
                'payout_account_type'   => $provider,
                'payout_account_number' => $phone,
                'payout_account_holder' => strip_tags(trim((string) $request->input('payout_account_holder', ''))) ?: null,
                'payout_id_number'      => strip_tags(trim((string) $request->input('payout_id_number', ''))) ?: null,
            ], fn ($v) => true);
        }

        // Cuenta Bancaria Tradicional
        $request->merge([
            'payout_account_number' => preg_replace('/[^0-9\-]/', '', (string) $request->input('payout_account_number')),
        ]);

        $request->validate([
            'payout_bank_name'      => ['required', 'string', 'max:100'],
            'payout_account_type'   => ['required', 'string', 'in:Ahorros,Corriente'],
            'payout_account_number' => ['required', 'string', 'min:6', 'max:30', 'regex:/^[0-9\-]{6,30}$/'],
            'payout_account_holder' => ['nullable', 'string', 'max:150'],
            'payout_id_number'      => ['nullable', 'string', 'max:40'],
        ], [
            'payout_bank_name.required'      => 'Selecciona la entidad bancaria de tu cuenta.',
            'payout_account_type.required'   => 'Selecciona el tipo de cuenta bancaria (Ahorros o Corriente).',
            'payout_account_type.in'         => 'El tipo de cuenta bancaria debe ser Ahorros o Corriente.',
            'payout_account_number.required' => 'Ingresa el número de tu cuenta bancaria.',
            'payout_account_number.min'      => 'El número de cuenta bancaria debe tener al menos 6 dígitos.',
            'payout_account_number.regex'    => 'El número de cuenta bancaria solo debe contener números.',
        ]);

        return [
            'payout_method_type'    => 'bank',
            'payout_bank_name'      => strip_tags(trim((string) $request->input('payout_bank_name'))),
            'payout_account_type'   => strip_tags(trim((string) $request->input('payout_account_type'))),
            'payout_account_number' => strip_tags(trim((string) $request->input('payout_account_number'))),
            'payout_account_holder' => strip_tags(trim((string) $request->input('payout_account_holder', ''))) ?: null,
            'payout_id_number'      => strip_tags(trim((string) $request->input('payout_id_number', ''))) ?: null,
        ];
    }
}
