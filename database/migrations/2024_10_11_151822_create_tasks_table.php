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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('assigned_to')->nullable()->constrained('users', 'id')->onDelete('cascade'); // user_id
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority');
            $table->string('status')->default('open');  // status = open , in-progress , completed , closed
            $table->date('due_date');
            $table->softDeletes(); 
            $table->timestamps();
            
            // Indexes for optimization
            $table->index('title');        
            $table->index('priority');     
            $table->index('status');       
            $table->index('due_date');     
            $table->index('assigned_to');  
            $table->index('deleted_at');   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
