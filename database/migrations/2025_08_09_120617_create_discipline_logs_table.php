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
        Schema::create('discipline_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('The student concerned')->constrained('users')->onDelete('cascade');
            $table->foreignId('recorded_by_id')->comment('The staff member who recorded the log')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['commendation', 'incident']);
            $table->date('log_date');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discipline_logs');
    }
};
