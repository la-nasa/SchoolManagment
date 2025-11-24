<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Evaluation extends Model implements AuditableContract
{
    use Auditable, HasFactory;
    protected $fillable = [
        'title',
        'exam_date',
        'class_id',
        'subject_id',
        'exam_type_id',
        'term_id',
        'school_year_id',
        'max_marks',
        'pass_marks',
        'description'
    ];

    protected $casts = [
        'exam_date' => 'date',
        'max_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2'
    ];

    // Relations
    public function class()
    {
        return $this->belongsTo(Classe::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    // Vérifier si l'évaluation est complétée (tous les élèves notés)
    public function getIsCompletedAttribute()
    {
        $totalStudents = $this->class->students()->count();
        $markedStudents = $this->marks()->count();

        return $totalStudents === $markedStudents;
    }

    // Pourcentage de complétion
    public function getCompletionPercentageAttribute()
    {
        $totalStudents = $this->class->students()->count();
        if ($totalStudents === 0) return 0;

        $markedStudents = $this->marks()->count();
        return ($markedStudents / $totalStudents) * 100;
    }
}
