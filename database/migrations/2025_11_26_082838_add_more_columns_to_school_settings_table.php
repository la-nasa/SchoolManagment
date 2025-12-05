<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreColumnsToSchoolSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('school_settings', function (Blueprint $table) {
            // Paramètres académiques
            $table->string('school_acronym')->nullable()->after('school_name');
            $table->date('trimester1_start')->nullable();
            $table->date('trimester2_start')->nullable();
            $table->date('trimester3_start')->nullable();
            $table->integer('sequences_per_trimester')->nullable()->default(3);
            $table->integer('grading_system')->nullable()->default(20);
            $table->decimal('passing_mark', 3, 1)->nullable()->default(10.0);
            $table->decimal('excellent_mark', 3, 1)->nullable()->default(16.0);
            $table->string('calculation_method')->nullable()->default('weighted');
            $table->string('rounding_method')->nullable()->default('half');

            // Paramètres de sécurité
            $table->integer('session_timeout')->nullable()->default(120);
            $table->integer('max_login_attempts')->nullable()->default(5);
            $table->boolean('password_uppercase')->nullable()->default(false);
            $table->boolean('password_lowercase')->nullable()->default(false);
            $table->boolean('password_numbers')->nullable()->default(false);
            $table->boolean('password_symbols')->nullable()->default(false);
            $table->integer('password_min_length')->nullable()->default(8);
            $table->integer('password_expiry_days')->nullable()->default(90);

            // Paramètres d'apparence
            $table->string('primary_color')->nullable()->default('#1e40af');
            $table->string('secondary_color')->nullable()->default('#f59e0b');
            $table->string('theme')->nullable()->default('light');
        });
    }

    public function down()
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                'school_acronym',
                'trimester1_start',
                'trimester2_start',
                'trimester3_start',
                'sequences_per_trimester',
                'grading_system',
                'passing_mark',
                'excellent_mark',
                'calculation_method',
                'rounding_method',
                'session_timeout',
                'max_login_attempts',
                'password_uppercase',
                'password_lowercase',
                'password_numbers',
                'password_symbols',
                'password_min_length',
                'password_expiry_days',
                'primary_color',
                'secondary_color',
                'theme'
            ]);
        });
    }
}
