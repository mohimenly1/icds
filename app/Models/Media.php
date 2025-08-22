<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'path',
    ];

    /**
     * The screens that display the media file.
     */
    public function screens(): BelongsToMany
    {
        return $this->belongsToMany(Screen::class)->withPivot('order');
    }
    public function unifiedContentSets(): BelongsToMany
{
    return $this->belongsToMany(UnifiedContentSet::class, 'media_unified_content_set');
}

}
