<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'start_date',
        'end_date',
        'is_current',
        'school_year_id'
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Relations
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function averages()
    {
        return $this->hasMany(Average::class);
    }

    public function generalAverages()
    {
        return $this->hasMany(GeneralAverage::class);
    }

    // Marquer comme trimestre courant
    public function setAsCurrent()
    {
        // Désactiver tous les autres trimestres courants
        self::where('is_current', true)->update(['is_current' => false]);

        // Activer ce trimestre
        $this->update(['is_current' => true]);
    }

    // Récupérer le trimestre courant
    public static function current()
    {
        return self::where('is_current', true)->first();
    }
}
