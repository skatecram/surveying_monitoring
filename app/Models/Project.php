<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'number',
        'name',
        'bearbeiter',
        'threshold_warning',
        'threshold_caution',
        'threshold_alarm',
    ];

    protected $casts = [
        'threshold_warning' => 'decimal:2',
        'threshold_caution' => 'decimal:2',
        'threshold_alarm' => 'decimal:2',
    ];

    public function nullMeasurements()
    {
        return $this->hasMany(NullMeasurement::class);
    }

    public function controlMeasurements()
    {
        return $this->hasMany(ControlMeasurement::class);
    }
}
