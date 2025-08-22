<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Doctor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'specialty',
        'bio',
        'photo_url',
        'status',
    ];

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }
    /**
     * Get the schedules for the doctor.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
