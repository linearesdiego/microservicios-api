<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')
                ->constrained()
                ->onDelete('cascade'); // Si se elimina el post, se eliminan sus archivos adjuntos
            // Información del archivo
            $table->string('mime_type');                   // Tipo MIME (image/jpeg, etc.)
            $table->string('path');          // Ruta donde se guardó el archivo
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
