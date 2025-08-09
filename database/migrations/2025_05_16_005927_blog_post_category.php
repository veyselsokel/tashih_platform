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
        Schema::create('blog_post_category', function (Blueprint $table) {
            $table->foreignId('blog_post_id')->constrained()->onDelete('cascade'); // BlogPost'a bağlı
            $table->foreignId('category_id')->constrained()->onDelete('cascade');  // Category'ye bağlı
            $table->primary(['blog_post_id', 'category_id']); // İki alanı birden birincil anahtar yap
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_category');
    }
};
