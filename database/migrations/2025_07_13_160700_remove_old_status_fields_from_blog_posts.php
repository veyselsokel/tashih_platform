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
        Schema::table('blog_posts', function (Blueprint $table) {
            // Remove the old is_published and is_draft fields since we're using status
            if (Schema::hasColumn('blog_posts', 'is_draft')) {
                $table->dropColumn('is_draft');
            }
            
            // Remove the formatting_new field that's not being used
            if (Schema::hasColumn('blog_posts', 'formatting_new')) {
                $table->dropColumn('formatting_new');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Restore the old fields if needed
            $table->boolean('is_draft')->default(true);
            $table->longText('formatting_new')->nullable();
        });
    }
};