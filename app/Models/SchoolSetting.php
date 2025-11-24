<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
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
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public static function getSettings()
    {
        return self::where('is_active', true)->first() ?? new self();
    }
}