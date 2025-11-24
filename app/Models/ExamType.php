<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'weight',
        'max_marks',
        'is_sequence',
        'is_term',
        'order'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_sequence' => 'boolean',
        'is_term' => 'boolean'
    ];

    // Relations
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    // Scope pour les types de séquence
    public function scopeSequences($query)
    {
        return $query->where('is_sequence', true);
    }

    // Scope pour les types de trimestre
    public function scopeTerms($query)
    {
        return $query->where('is_term', true);
    }
}
