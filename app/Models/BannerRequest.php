<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerRequest extends Model
{
    protected $fillable = [
        'user_id',
        'image_path',
        'title',
        'payment_proof_path',
        'status',
        'payment_status',
        'payment_transaction_id',
        'paid_at',
        'refund_status',
        'cost',
        'notes',
        'rejection_reason',
        'target_type',
        'selected_products',
        'target_url',
        'payout_destination_mode',
        'payout_method_type',
        'payout_bank_name',
        'payout_account_type',
        'payout_account_number',
        'payout_account_holder',
    ];

    protected $casts = [
        'selected_products' => 'array',
        'cost' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function isCorporateDestination(): bool
    {
        return ($this->payout_destination_mode ?: 'corporate') === 'corporate';
    }

    public function formattedPayoutDestination(): string
    {
        if (!empty($this->payout_bank_name) && !empty($this->payout_account_number)) {
            if ($this->payout_method_type === 'wallet' || in_array($this->payout_account_type, ['Nequi', 'Daviplata'], true)) {
                return "{$this->payout_bank_name} (Cel: {$this->payout_account_number})";
            }
            $type = $this->payout_account_type ? " {$this->payout_account_type}" : '';
            return "{$this->payout_bank_name}{$type} • {$this->payout_account_number}";
        }

        $snapshot = Setting::activeBannerPayoutSnapshot();
        return $snapshot['summary_label'];
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image_path) {
            return '';
        }
        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }
        return asset('storage/' . ltrim($this->image_path, '/'));
    }

    public function getIsVideoAttribute(): bool
    {
        if (!$this->image_path) {
            return false;
        }
        $ext = strtolower(pathinfo($this->image_path, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'webm', 'mov'], true);
    }
}
