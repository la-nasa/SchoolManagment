<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'section',
        'capacity',
        'teacher_id',
        'school_year_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relations
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'class_id');
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    // Scope pour les classes actives
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Nom complet de la classe
    public function getFullNameAttribute()
    {
        return $this->name . ($this->section ? ' ' . $this->section : '');
    }

     public function generalAverages()
    {
        return $this->hasMany(GeneralAverage::class);
    }
}
