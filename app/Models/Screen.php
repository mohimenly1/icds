<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Screen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'unique_key',
        'is_active',
        'is_unified_content',
        'unified_content_set_id', // إضافة الحقل الجديد
    ];

    /**
     * The media files that belong to the screen.
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class)->withPivot('order')->orderBy('order');
    }
    public function unifiedContentSet(): BelongsTo
    {
        return $this->belongsTo(UnifiedContentSet::class);
    }
}
