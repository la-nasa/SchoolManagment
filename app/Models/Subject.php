<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Subject extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'coefficient',
        'description',
        'is_active'
    ];

    protected $casts = [
        'coefficient' => 'integer',
        'is_active' => 'boolean'
    ];

    // Relations
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_assignments', 'subject_id', 'teacher_id')
                    ->withPivot('class_id', 'is_titular')
                    ->withTimestamps();
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'teacher_assignments', 'subject_id', 'class_id')
                    ->withPivot('teacher_id', 'is_titular')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithHighCoefficient($query, $threshold = 5)
    {
        return $query->where('coefficient', '>=', $threshold);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->code})";
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active ?
            '<span class="badge badge-success">Active</span>' :
            '<span class="badge badge-danger">Inactive</span>';
    }

    // Méthodes
    public function hasTeachers()
    {
        return $this->teachers()->exists();
    }

    public function getAssignedTeachersCount()
    {
        return $this->teachers()->count();
    }
}
