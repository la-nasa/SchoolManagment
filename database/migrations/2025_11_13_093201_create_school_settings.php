<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->text('school_address')->nullable();
            $table->string('school_phone')->nullable();
            $table->string('school_email')->nullable();
            $table->string('school_website')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('principal_title')->nullable();
            $table->string('school_logo')->nullable();
            $table->string('school_city')->nullable();
            $table->string('school_country')->nullable();
            $table->text('bulletin_header_fr')->nullable();
            $table->text('bulletin_header_en')->nullable();
            $table->text('bulletin_footer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('school_settings');
    }
};