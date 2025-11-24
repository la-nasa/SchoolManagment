<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Mark extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'student_id',
        'evaluation_id',
        'subject_id',
        'class_id',
        'term_id',
        'school_year_id',
        'marks',
        'comment',
        'is_absent'
    ];

    protected $casts = [
        'marks' => 'decimal:2',
        'is_absent' => 'boolean'
    ];

    // Relations
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function class()
    {
        return $this->belongsTo(Classe::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    // Appréciation basée sur les notes
    public function getAppreciationAttribute()
    {
        if ($this->is_absent) {
            return 'Absent';
        }

        $percentage = ($this->marks / $this->evaluation->max_marks) * 100;

        if ($percentage >= 80) return 'Excellent';
        if ($percentage >= 70) return 'Très bien';
        if ($percentage >= 60) return 'Bien';
        if ($percentage >= 50) return 'Assez bien';
        if ($percentage >= 40) return 'Passable';
        return 'Insuffisant';
    }

    // Vérifier si l'élève a réussi
    public function getIsPassedAttribute()
    {
        if ($this->is_absent) return false;
        return $this->marks >= $this->evaluation->pass_marks;
    }
}
