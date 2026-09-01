<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
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
        'kyc_status',
        'business_type',
        'employee_count'
    ];

    protected function casts(): array
    {
        return [
            'banners_path' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }
}
