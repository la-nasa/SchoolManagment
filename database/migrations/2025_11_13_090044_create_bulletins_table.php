<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bulletins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->decimal('average', 5, 2);
            $table->integer('rank');
            $table->text('appreciation');
            $table->text('head_teacher_comment')->nullable();
            $table->text('principal_comment')->nullable();
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->unique(['student_id', 'school_year_id', 'term_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bulletins');
    }
};