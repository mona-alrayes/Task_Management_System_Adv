<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('exception_type')->nullable();   // Exception class
            $table->text('message');                        // Error message
            $table->text('trace');                          // Error trace
            $table->string('file');                         // File where the error occurred
            $table->integer('line');                        // Line number in the file
            $table->string('url')->nullable();              // URL where the error occurred
            $table->string('method')->nullable();           // Request method
            $table->json('input')->nullable();              // Request input
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
