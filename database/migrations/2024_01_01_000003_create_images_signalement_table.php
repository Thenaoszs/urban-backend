<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images_signalement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signalement_id')
                  ->constrained('signalements')
                  ->cascadeOnDelete(); // suppression en cascade si le signalement est supprimé
            $table->string('image_path', 500); // chemin relatif : signalements/xxx.jpg
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images_signalement');
    }
};
