<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PQRS extends Model
{
    protected $table = 'p_q_r_s';

    protected $fillable = [
        'user_id',
        'type',
        'subject',
        'content',
        'status',
        'admin_response',
        'resolved_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
