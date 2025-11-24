<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Student extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'matricule',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'birth_place',
        'photo',
        'class_id',
        'school_year_id',
        'is_active'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Relations
    public function class()
    {
        return $this->belongsTo(Classe::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
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

    // Nom complet de l'élève
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Générer un matricule automatique
    public static function generateMatricule()
    {
        $prefix = 'ELE';
        $year = date('Y');
        $lastStudent = self::where('matricule', 'like', $prefix . $year . '%')
            ->orderBy('matricule', 'desc')
            ->first();

        $sequence = $lastStudent ? (int)substr($lastStudent->matricule, -4) + 1 : 1;
        return $prefix . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Calculer l'âge
    public function getAgeAttribute()
    {
        return $this->birth_date->age;
    }

    // Scope pour les étudiants actifs
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
