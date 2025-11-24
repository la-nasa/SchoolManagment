<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_year_id')->constrained()->onDelete('cascade');
            $table->boolean('is_titular')->default(false);
            $table->timestamps();

            $table->unique(
                ['teacher_id', 'class_id', 'subject_id', 'school_year_id'],
                'teacher_assign_unique'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('teacher_assignments');
    }
};
