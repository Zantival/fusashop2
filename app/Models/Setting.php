<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'description'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (!Schema::hasTable('settings')) {
            return $default;
        }

        return Cache::remember("setting.{$key}", 300, function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', ?string $description = null): self
    {
        if ($key === 'banner_payout_config') {
            $config = json_decode((string) $value, true);
            if (is_array($config)) {
                foreach (['custom_wompi_integrity_secret', 'custom_wompi_events_secret'] as $secretKey) {
                    $secret = (string) ($config[$secretKey] ?? '');
                    if ($secret !== '' && !str_starts_with($secret, 'enc:v1:')) {
                        $config[$secretKey] = 'enc:v1:' . Crypt::encryptString($secret);
                    }
                }
                $value = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        $setting = static::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => (string) $value,
                'group' => $group,
                'description' => $description,
            ], fn ($v) => $v !== null)
        );

        Cache::forget("setting.{$key}");
        return $setting;
    }

    public static function commissionRate(): float
    {
        return (float) static::getValue('commission_rate', 8.0);
    }

    public static function bannerFee(): int
    {
        return (int) static::getValue('banner_fee', 150000);
    }

    public static function promoCardFee(): int
    {
        return (int) static::getValue('promo_card_fee', 150000);
    }

    public static function pqrsSlaBusinessDays(): int
    {
        return (int) static::getValue('pqrs_sla_business_days', 15);
    }

    public static function defaultBannerPayoutConfig(): array
    {
        return [
            'destination_mode'              => 'corporate', // 'corporate' | 'custom'
            'corporate_bank_name'           => 'Bancolombia (Cuenta Corporativa Central FusaShop)',
            'corporate_account_type'        => 'Corriente',
            'corporate_account_number'      => '031-998877-01',
            'corporate_account_holder'      => 'FusaShop S.A.S. — Recaudo Central',
            'corporate_nit'                 => '901.456.789-1',
            'custom_method_type'            => 'bank', // 'bank' | 'wallet'
            'custom_bank_name'              => 'Bancolombia',
            'custom_account_type'           => 'Ahorros', // 'Ahorros' | 'Corriente'
            'custom_account_number'         => '',
            'custom_wallet_provider'        => 'Nequi', // 'Nequi' | 'Daviplata'
            'custom_wallet_phone'           => '',
            'custom_account_holder'         => '',
            'custom_id_number'              => '',
            'custom_wompi_public_key'       => '',
            'custom_wompi_integrity_secret' => '',
            'custom_wompi_events_secret'    => '',
            'updated_by'                    => null,
            'updated_at'                    => null,
        ];
    }

    public static function bannerPayoutConfig(): array
    {
        $raw = static::getValue('banner_payout_config');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach (['custom_wompi_integrity_secret', 'custom_wompi_events_secret'] as $secretKey) {
                    $secret = (string) ($decoded[$secretKey] ?? '');
                    if (str_starts_with($secret, 'enc:v1:')) {
                        try {
                            $decoded[$secretKey] = Crypt::decryptString(substr($secret, 7));
                        } catch (\Throwable) {
                            $decoded[$secretKey] = '';
                        }
                    }
                }
                return array_merge(static::defaultBannerPayoutConfig(), $decoded);
            }
        }

        return static::defaultBannerPayoutConfig();
    }

    public static function activeBannerPayoutSnapshot(): array
    {
        $cfg = static::bannerPayoutConfig();
        $mode = ($cfg['destination_mode'] ?? 'corporate') === 'custom' ? 'custom' : 'corporate';

        if ($mode === 'custom') {
            $methodType = ($cfg['custom_method_type'] ?? 'bank') === 'wallet' ? 'wallet' : 'bank';
            if ($methodType === 'wallet') {
                $bankName = $cfg['custom_wallet_provider'] ?: 'Nequi';
                $accountType = $bankName;
                $accountNumber = $cfg['custom_wallet_phone'] ?: 'Sin número';
                $holder = $cfg['custom_account_holder'] ?: 'Recaudo Publicidad Analista';
                $summary = "Billetera {$bankName} • Cel: {$accountNumber}";
            } else {
                $bankName = $cfg['custom_bank_name'] ?: 'Bancolombia';
                $accountType = $cfg['custom_account_type'] ?: 'Ahorros';
                $accountNumber = $cfg['custom_account_number'] ?: 'Sin número';
                $holder = $cfg['custom_account_holder'] ?: 'Recaudo Publicidad Analista';
                $summary = "Cuenta {$accountType} {$bankName} • Nº {$accountNumber}";
            }

            return [
                'payout_destination_mode' => 'custom',
                'payout_method_type'      => $methodType,
                'payout_bank_name'        => $bankName,
                'payout_account_type'     => $accountType,
                'payout_account_number'   => $accountNumber,
                'payout_account_holder'   => $holder,
                'mode_badge'              => 'Cuenta Específica Analista',
                'summary_label'           => $summary,
                'wompi_public_key'        => $cfg['custom_wompi_public_key'] ?? null,
                'wompi_integrity_secret'  => $cfg['custom_wompi_integrity_secret'] ?? null,
                'wompi_events_secret'     => $cfg['custom_wompi_events_secret'] ?? null,
            ];
        }

        $corpBank = $cfg['corporate_bank_name'] ?: 'Bancolombia (Cuenta Corporativa Central FusaShop)';
        $corpType = $cfg['corporate_account_type'] ?: 'Corriente';
        $corpNum  = $cfg['corporate_account_number'] ?: '031-998877-01';
        $corpHolder = $cfg['corporate_account_holder'] ?: 'FusaShop S.A.S. — Recaudo Central';

        return [
            'payout_destination_mode' => 'corporate',
            'payout_method_type'      => 'corporate',
            'payout_bank_name'        => $corpBank,
            'payout_account_type'     => $corpType,
            'payout_account_number'   => $corpNum,
            'payout_account_holder'   => $corpHolder,
            'mode_badge'              => 'Cuenta Corporativa Central FusaShop',
            'summary_label'           => "{$corpBank} ({$corpType} • {$corpNum})",
            'wompi_public_key'        => null,
            'wompi_integrity_secret'  => null,
            'wompi_events_secret'     => null,
        ];
    }

    public static function howToStartGuide(): array
    {
        $raw = static::getValue('how_to_start_guide');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                // Estos textos son afirmaciones de seguridad/finanzas y deben
                // conservarse veraces incluso si existe una configuración antigua.
                return array_replace($decoded, [
                    'card_subtitle' => 'Elige si quieres comprar en el catálogo o registrar tu tienda y conoce los pasos desde la plataforma.',
                    'buyer_description' => 'Explora comercios de Fusagasugá, revisa las condiciones de compra y consulta el estado de tu pedido desde tu cuenta.',
                    'buyer_video_url' => '',
                    'seller_video_url' => '',
                    'buyer_steps' => "1. Crea tu cuenta y confirma el correo con el código recibido.\n2. Explora productos y revisa precio, disponibilidad y condiciones.\n3. Confirma el pedido y consulta su estado desde tu cuenta.",
                    'seller_why_verify' => 'La revisión de documentos ayuda a validar la información de tu tienda y puede habilitar funciones que requieren verificación. El resultado y los requisitos se informan en tu panel; la revisión no garantiza ventas ni elimina obligaciones legales o tributarias.',
                    'seller_description' => 'Conoce los requisitos para registrar tu tienda y solicitar una revisión de documentos desde tu cuenta.',
                    'seller_steps' => "1. Crea tu cuenta de comerciante y confirma el correo.\n2. Completa los datos de tu tienda y carga solo los documentos solicitados en el panel.\n3. Consulta el estado y las observaciones de la revisión en tu cuenta.",
                ]);
            }
        }

        return [
            'card_badge'        => '¿Nuevo en FusaShop?',
            'card_title'        => '¿No sabes cómo empezar?',
            'card_subtitle'     => 'Haz clic aquí y te guiamos en 1 minuto: elige si deseas comprar productos locales o formalizar, vender y certificar tu negocio en Fusagasugá.',
            'card_button_text'  => 'Comenzar Aquí',
            
            // Comprador
            'buyer_title'       => '¿Cómo Comprar en FusaShop?',
            'buyer_description' => 'Explora comercios de Fusagasugá, revisa las condiciones de compra y consulta el estado de tu pedido desde tu cuenta.',
            'buyer_video_url'   => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'buyer_steps'       => "1. Crea tu cuenta y confirma el correo con el código recibido.\n2. Explora productos y revisa precio, disponibilidad y condiciones.\n3. Confirma el pedido y consulta su estado desde tu cuenta.",

            // Vendedor
            'seller_title'       => '¿Cómo Vender y Formalizar tu Negocio?',
            'seller_description' => 'Conoce los requisitos para registrar tu tienda y solicitar una revisión de documentos desde tu cuenta.',
            'seller_video_url'   => '',
            'seller_why_verify'  => 'La revisión de documentos ayuda a validar la información de tu tienda y puede habilitar funciones que requieren verificación. El resultado y los requisitos se informan en tu panel; la revisión no garantiza ventas ni elimina obligaciones legales o tributarias.',
            'seller_steps'       => "1. Crea tu cuenta de comerciante y confirma el correo.\n2. Completa los datos de tu tienda y carga solo los documentos solicitados en el panel.\n3. Consulta el estado y las observaciones de la revisión en tu cuenta.",
            'dian_url'           => 'https://www.dian.gov.co',
            'rues_url'           => 'https://www.rues.org.co'
        ];
    }

    public static function formatVideoEmbedUrl(?string $url): ?string
    {
        if (!$url) return null;
        $url = trim($url);
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $url, $matches)) {
            return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
        }
        if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[3];
        }
        return $url;
    }
}
