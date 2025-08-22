<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UnifiedContentSet extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'is_active'];

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_unified_content_set');
    }
}