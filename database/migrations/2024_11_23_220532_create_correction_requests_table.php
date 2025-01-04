<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('client_name');
            $table->string('email');
            $table->string('document_type');
            $table->text('description');
            $table->string('attachment_path');
            $table->enum('status', ['pending', 'in-progress', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
