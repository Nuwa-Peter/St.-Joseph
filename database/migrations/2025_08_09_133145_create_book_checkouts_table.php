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
        Schema::create('book_checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('checkout_date');
            $table->dateTime('due_date');
            $table->timestamp('returned_date')->nullable();
            $table->decimal('fine_amount', 8, 2)->nullable();
            $table->foreignId('checked_out_by_id')->constrained('users'); // Librarian
            $table->foreignId('checked_in_by_id')->nullable()->constrained('users'); // Librarian
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_checkouts');
    }
};
