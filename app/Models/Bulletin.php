<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bulletin extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'school_year_id',
        'term_id',
        'average',
        'rank',
        'appreciation',
        'head_teacher_comment',
        'principal_comment',
        'generated_by',
        'generated_at'
    ];

    protected $casts = [
        'average' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function marks()
    {
        return $this->hasMany(Mark::class, 'student_id', 'student_id')
            ->where('school_year_id', $this->school_year_id)
            ->where('term_id', $this->term_id);
    }
}