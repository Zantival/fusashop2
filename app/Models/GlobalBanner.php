<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalBanner extends Model
{
    protected $fillable = ['title', 'image_path', 'link_url', 'is_active', 'sort_order'];
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
