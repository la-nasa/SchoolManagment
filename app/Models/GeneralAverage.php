<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralAverage extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'classe_id',
        'term_id',
        'school_year_id',
        'average',
        'rank',
        'appreciation',
        'total_students'
    ];

    protected $casts = [
        'average' => 'decimal:2'
    ];

    // Relations
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    // Appréciation basée sur la moyenne générale
    public function getAppreciationAttribute($value)
    {
        if ($value) return $value;

        if ($this->average >= 18) return 'Excellent';
        if ($this->average >= 16) return 'Très bien';
        if ($this->average >= 14) return 'Bien';
        if ($this->average >= 12) return 'Assez bien';
        if ($this->average >= 10) return 'Passable';
        return 'Insuffisant';
    }
}
