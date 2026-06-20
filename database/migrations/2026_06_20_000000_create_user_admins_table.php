<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('first_access')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletesTz('DeletionDate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_admins');
    }
};
