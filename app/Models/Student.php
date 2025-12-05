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

    protected $appends = ['full_name'];

    // Relations
    public function class()
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function getClasseAttribute()
{
    return $this->classe()->first();
}

    //  public function class()
    // {
    //     return $this->classe();
    // }

     public function hasClass()
    {
        return !is_null($this->class_id);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function averages()
    {
        return $this->hasMany(Average::class, 'student_id');
    }

    public function generalAverages()
    {
        return $this->hasMany(GeneralAverage::class, 'student_id');
    }

    // Nom complet de l'élève
    public function getFullNameAttribute()
    {
        $firstName = $this->first_name ?? '';
        $lastName = $this->last_name ?? '';
        return trim($firstName . ' ' . $lastName);
    }

     public function getFullName()
    {
        return $this->full_name;
    }

    // Méthode pour obtenir le prénom
    // public function getFirstNameAttribute()
    // {
    //     return $this->user ? $this->user->first_name : '';
    // }

    // // Méthode pour obtenir le nom
    // public function getLastNameAttribute()
    // {
    //     return $this->user ? $this->user->last_name : '';
    // }


    public function getPhotoUrlAttribute()
    {
        if ($this->photo && file_exists(storage_path('app/public/' . $this->photo))) {
            return asset('storage/' . $this->photo);
        }
        return '/images/default-avatar.png';
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

    public function scopeWithClass($query)
{
    return $query->whereNotNull('class_id')->where('class_id', '>', 0);
}

    public function canGenerateBulletin()
    {
        return $this->hasClass() && $this->is_active;
    }

    public function getClassName()
    {
        if (!$this->hasClass()) {
            return 'Non assigné';
        }
        
        // Charger la relation si pas déjà chargée
        if (!$this->relationLoaded('classe')) {
            $this->load('classe');
        }
        
        return $this->classe ? ($this->classe->full_name ?? $this->classe->name ?? 'Classe inconnue') : 'Classe inconnue';
    }

    // public function getFullName()
    // {
    //     return $this->first_name . ' ' . $this->last_name;
    // }

    //  public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation classe (alias de class pour compatibilité)
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    
     

}
