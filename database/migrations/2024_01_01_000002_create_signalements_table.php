<?php

use App\Models\Signalement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signalements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->enum('type', Signalement::TYPES);
            $table->text('description');
            $table->decimal('latitude',  10, 7);
            $table->decimal('longitude', 10, 7);
            $table->enum('statut', Signalement::STATUTS)->default('en_cours');
            $table->timestamps(); // created_at = date_creation
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signalements');
    }
};
