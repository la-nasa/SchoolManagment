<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'school_acronym',
        'school_address',
        'school_phone',
        'school_email',
        'school_website',
        'principal_name',
        'principal_title',
        'school_logo',
        'school_city',
        'school_country',
        'bulletin_header_fr',
        'bulletin_header_en',
        'bulletin_footer',
        'is_active',
        // Paramètres académiques
        'trimester1_start',
        'trimester2_start',
        'trimester3_start',
        'sequences_per_trimester',
        'grading_system',
        'passing_mark',
        'excellent_mark',
        'calculation_method',
        'rounding_method',
        // Paramètres de sécurité
        'session_timeout',
        'max_login_attempts',
        'password_uppercase',
        'password_lowercase',
        'password_numbers',
        'password_symbols',
        'password_min_length',
        'password_expiry_days',
        // Paramètres d'apparence
        'primary_color',
        'secondary_color',
        'theme'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password_uppercase' => 'boolean',
        'password_lowercase' => 'boolean',
        'password_numbers' => 'boolean',
        'password_symbols' => 'boolean',
        'trimester1_start' => 'date',
        'trimester2_start' => 'date',
        'trimester3_start' => 'date',
    ];

    protected $attributes = [
        'is_active' => true,
        'grading_system' => 20,
        'passing_mark' => 10.0,
        'excellent_mark' => 16.0,
        'calculation_method' => 'weighted',
        'rounding_method' => 'half',
        'sequences_per_trimester' => 3,
        'session_timeout' => 120,
        'max_login_attempts' => 5,
        'password_min_length' => 8,
        'password_expiry_days' => 90,
        'primary_color' => '#1e40af',
        'secondary_color' => '#f59e0b',
        'theme' => 'light',
    ];

    public static function getSettings()
    {
        return self::where('is_active', true)->first() ?? new self();
    }

    /**
     * Récupérer la valeur d'un paramètre
     */
    public function getSetting($key, $default = null)
    {
        return $this->$key ?? $default;
    }

    /**
     * Définir la valeur d'un paramètre
     */
    public function setSetting($key, $value)
    {
        $this->$key = $value;
        return $this->save();
    }
}
