<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'is_current',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Relations
    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    // Marquer comme année courante
    public function setAsCurrent()
    {
        // Désactiver toutes les autres années courantes
        self::where('is_current', true)->update(['is_current' => false]);

        // Activer cette année
        $this->update(['is_current' => true]);
    }

    // Récupérer l'année scolaire courante
    public static function current()
    {
        return self::where('is_current', true)->first();
    }

//     public static function current()
// {
//     return static::where('is_current', true)->first();
// }


}
