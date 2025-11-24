<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('averages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_year_id')->constrained()->onDelete('cascade');
            $table->decimal('average', 5, 2);
            $table->integer('rank')->nullable();
            $table->text('appreciation')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'term_id', 'school_year_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('averages');
    }
};
