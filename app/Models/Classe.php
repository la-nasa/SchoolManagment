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

    public function headTeacher()
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
        return $this->hasMany(Evaluation::class, 'class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subjects', 'class_id', 'subject_id');
    }



    // Scope pour les classes actives
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

     public function scopeOfLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeOfSchoolYear($query, $schoolYearId)
    {
        return $query->where('school_year_id', $schoolYearId);
    }

    // Nom complet de la classe
    public function getFullNameAttribute()
    {
        $name = $this->name;

        if ($this->level) {
            $name = $this->level . ' ' . $name;
        }

        if ($this->section) {
            $name .= ' ' . $this->section;
        }

        return $name;
    }

     public function generalAverages()
    {
        return $this->hasMany(GeneralAverage::class, 'classe_id');
    }

    public function averages()
{
    return $this->hasManyThrough(Average::class, Student::class, 'class_id', 'student_id');
}
// Méthode pour obtenir les enseignants assignés
    public function assignedTeachers()
    {
        return $this->belongsToMany(User::class, 'teacher_assignments', 'class_id', 'teacher_id')
            ->withPivot(['subject_id', 'is_titular', 'school_year_id'])
            ->withTimestamps();
    }

    // Méthode pour vérifier si la classe est complète
    public function isFull()
    {
        return $this->students()->count() >= $this->capacity;
    }

     public function resetData()
    {
        // Supprimer toutes les données associées (à utiliser avec précaution)
        DB::transaction(function () {
            $this->evaluations()->delete();
            $this->generalAverages()->delete();

            // Pour les étudiants, on peut les garder mais réinitialiser leurs moyennes
            Average::where('classe_id', $this->id)->delete();

            // Supprimer les affectations des enseignants
            $this->teacherAssignments()->delete();
        });
    }
}
