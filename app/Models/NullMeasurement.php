<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NullMeasurement extends Model
{
    protected $fillable = [
        'project_id',
        'punkt',
        'E',
        'N',
        'H',
        'date',
    ];

    protected $casts = [
        'E' => 'decimal:6',
        'N' => 'decimal:6',
        'H' => 'decimal:6',
        'date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
