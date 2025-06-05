<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'location',
        'division_id',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Get the division that owns the event.
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
