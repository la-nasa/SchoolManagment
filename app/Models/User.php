<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements AuditableContract
{
    use Auditable, HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'matricule',
        'phone',
        'address',
        'birth_date',
        'gender',
        'photo',
        'is_active',
        'last_login_at',
        'password_changed_at',
        'class_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'role_name',
        'initials'
    ];

    // Relations
    public function class()
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

     public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function assignedClasses()
    {
        return $this->belongsToMany(Classe::class, 'teacher_assignments', 'teacher_id', 'class_id')
            ->withPivot('subject_id', 'is_titular')
            ->withTimestamps();
    }

    public function assignedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_assignments', 'teacher_id', 'subject_id')
            ->withPivot('class_id', 'is_titular')
            ->withTimestamps();
    }

    // Méthodes de vérification de rôle
    public function isTeacher()
    {
        return $this->hasRole('enseignant');
    }

    public function isTitularTeacher()
    {
        return $this->hasRole('enseignant titulaire');
    }

    public function isAdministrator()
    {
        return $this->hasRole('administrateur');
    }

    public function isDirector()
    {
        return $this->hasRole('directeur');
    }

    public function isSecretary()
    {
        return $this->hasRole('secretaire');
    }

    // Générer un matricule automatique
    public static function generateMatricule()
    {
        $prefix = 'ENS';
        $year = date('Y');
        $lastUser = self::where('matricule', 'like', $prefix . $year . '%')
            ->orderBy('matricule', 'desc')
            ->first();

        $sequence = $lastUser ? (int)substr($lastUser->matricule, -4) + 1 : 1;
        return $prefix . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Générer un mot de passe temporaire
     */
    public static function generateTemporaryPassword($length = 10)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Vérifier si le mot de passe est temporaire
     */
    public function isPasswordTemporary()
    {
        return $this->password_changed_at === null &&
               $this->created_at->diffInDays(now()) <= 7;
    }

    /**
     * Marquer le mot de passe comme changé
     */
    public function markPasswordAsChanged()
    {
        $this->update(['password_changed_at' => now()]);
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtenir les classes assignées à cet enseignant
     */
    public function getAssignedClassesAttribute()
    {
        if (!$this->isTeacher() && !$this->isTitularTeacher()) {
            return collect();
        }

        return $this->assignedClasses()->with('students')->get();
    }

    /**
     * Obtenir les matières assignées à cet enseignant
     */
    public function getAssignedSubjectsAttribute()
    {
        if (!$this->isTeacher() && !$this->isTitularTeacher()) {
            return collect();
        }

        return $this->assignedSubjects()->get();
    }

    /**
     * Accessor pour le nom du rôle principal
     */
    public function getRoleNameAttribute()
    {
        return $this->roles->first()->name ?? 'Aucun rôle';
    }

    /**
     * Accessor pour les initiales (pour les avatars)
     */
    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->name);
        $initials = '';

        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }

        return substr($initials, 0, 2);
    }

    public function getFullName()
    {
        return $this->name;
    }

    /**
     * Obtenir le chemin de la photo ou une image par défaut
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }

        return "https://ui-avatars.com/api/?name={$this->initials}&color=FFFFFF&background=1e40af";
    }

    /**
     * Vérifier si l'utilisateur peut accéder à l'audit
     */
    public function canAccessAudit()
    {
        return $this->isAdministrator() ||
               ($this->isDirector() && $this->can('view-audit-trail'));
    }


}
