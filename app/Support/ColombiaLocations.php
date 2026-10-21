<?php

namespace App\Support;

class ColombiaLocations
{
    private static ?array $municipalities = null;

    private static array $aliases = [
        'bogota' => '11001',
        'bogotadc' => '11001',
        'distritocapital' => '11001',
        'santafedebogota' => '11001',
        'fusa' => '25290',
        'fusagasuga' => '25290',
        'ubate' => '25843',
        'villadesandiegodeubate' => '25843',
        'medellin' => '05001',
        'cali' => '76001',
        'santiagodecali' => '76001',
        'barranquilla' => '08001',
        'cartagena' => '13001',
        'cartagenadeindias' => '13001',
        'cucuta' => '54001',
        'sanjosedecucuta' => '54001',
        'bucaramanga' => '68001',
        'pereira' => '66001',
        'santamarta' => '47001',
        'ibague' => '73001',
        'pasto' => '52001',
        'sanjuandepasto' => '52001',
        'manizales' => '17001',
        'neiva' => '41001',
        'villavicencio' => '50001',
        'armenia' => '63001',
        'popayan' => '19001',
        'valledupar' => '20001',
        'monteria' => '23001',
        'sincelejo' => '70001',
        'riohacha' => '44001',
        'tunja' => '15001',
        'florencia' => '18001',
        'quibdo' => '27001',
        'yopal' => '85001',
        'mocoa' => '86001',
        'sanandres' => '88001',
        'leticia' => '91001',
        'inirida' => '94001',
        'sanjosedelguaviare' => '95001',
        'mitu' => '97001',
        'puertocarreno' => '99001',
        'chia' => '25175',
        'soacha' => '25754',
        'zipaquira' => '25899',
        'facatativa' => '25269',
        'girardot' => '25307',
    ];

    /** Mapeo de códigos internos / ISO a códigos de departamento reconocidos por Envia.com */
    private static array $enviaStateMap = [
        'AM' => 'AM', // Amazonas
        'AN' => 'AN', // Antioquia
        'AR' => 'AR', // Arauca
        'AT' => 'AT', // Atlántico
        'DC' => 'DC', // Bogotá D.C.
        'BL' => 'BL', // Bolívar
        'BY' => 'BY', // Boyacá
        'CL' => 'CL', // Caldas
        'CQ' => 'CA', // Caquetá
        'CS' => 'CS', // Casanare
        'CA' => 'CU', // Cauca
        'CE' => 'CE', // Cesar
        'CH' => 'CH', // Chocó
        'CO' => 'CO', // Córdoba
        'CU' => 'CN', // Cundinamarca -> Envia usa 'CN'
        'GN' => 'GU', // Guainía -> Envia usa 'GU'
        'GV' => 'GA', // Guaviare -> Envia usa 'GA'
        'HU' => 'HU', // Huila
        'LG' => 'LG', // La Guajira
        'MA' => 'MA', // Magdalena
        'ME' => 'ME', // Meta
        'NA' => 'NA', // Nariño
        'NS' => 'NS', // Norte de Santander
        'PU' => 'PU', // Putumayo
        'QD' => 'QU', // Quindío -> Envia usa 'QU'
        'RI' => 'RI', // Risaralda
        'SA' => 'SA', // San Andrés
        'ST' => 'SN', // Santander -> Envia usa 'SN'
        'SU' => 'SU', // Sucre
        'TO' => 'TO', // Tolima
        'VC' => 'VC', // Valle del Cauca
        'VD' => 'VA', // Vaupés -> Envia usa 'VA'
        'VI' => 'VI', // Vichada
    ];

    public static function all(): array
    {
        if (self::$municipalities === null) {
            $path = database_path('data/colombia_municipalities.json');
            if (file_exists($path)) {
                self::$municipalities = json_decode(file_get_contents($path), true) ?: [];
            } else {
                self::$municipalities = [];
            }
        }

        return self::$municipalities;
    }

    public static function normalize(string $str): string
    {
        $str = mb_strtolower(trim($str), 'UTF-8');
        $str = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $str
        );
        return preg_replace('/[^a-z0-9]/', '', $str);
    }

    public static function find(string $city, ?string $state = null): ?array
    {
        $normCity = self::normalize($city);
        if (empty($normCity)) {
            return null;
        }

        // Si es un alias conocido
        if (isset(self::$aliases[$normCity])) {
            $daneId = self::$aliases[$normCity];
            foreach (self::all() as $m) {
                if ($m['id'] === $daneId) {
                    return $m;
                }
            }
        }

        $matches = [];
        $normState = $state ? strtoupper(trim($state)) : null;

        foreach (self::all() as $m) {
            $mNorm = self::normalize($m['name']);
            if ($mNorm === $normCity || str_starts_with($mNorm, $normCity) || str_ends_with($mNorm, $normCity)) {
                // Coincidencia exacta o parcial
                if ($normState && ($m['state_code'] === $normState || $m['envia_state'] === $normState || self::normalize($m['dpt_name']) === self::normalize($state))) {
                    return $m; // Prioridad si coincide el departamento
                }
                $matches[] = $m;
            }
        }

        return $matches[0] ?? null;
    }

    public static function findDane8(string $city, ?string $state = null): ?string
    {
        // Si ya es un DANE de 8 dígitos
        if (preg_match('/^\d{8}$/', trim($city))) {
            return trim($city);
        }

        // Si es un DANE de 5 dígitos
        if (preg_match('/^\d{5}$/', trim($city))) {
            return trim($city) . '000';
        }

        $loc = self::find($city, $state);
        return $loc ? $loc['dane8'] : null;
    }

    public static function toEnviaStateCode(?string $state): string
    {
        if (empty($state)) {
            return 'CN';
        }

        $upper = strtoupper(trim($state));
        if (isset(self::$enviaStateMap[$upper])) {
            return self::$enviaStateMap[$upper];
        }

        // Si viene el nombre completo del departamento
        $norm = self::normalize($state);
        foreach (self::all() as $m) {
            if (self::normalize($m['dpt_name']) === $norm) {
                return $m['envia_state'];
            }
        }

        return strlen($upper) === 2 ? $upper : 'CN';
    }
}
