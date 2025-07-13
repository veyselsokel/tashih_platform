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
            // Add scheduled_at field if not exists
            if (!Schema::hasColumn('blog_posts', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('published_at');
            }
            
            // Remove is_published field as we're using status instead
            if (Schema::hasColumn('blog_posts', 'is_published')) {
                $table->dropColumn('is_published');
            }
            
            // Ensure status field exists with proper default
            if (!Schema::hasColumn('blog_posts', 'status')) {
                $table->string('status')->default('draft')->after('tags');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Restore is_published field
            if (!Schema::hasColumn('blog_posts', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('tags');
            }
            
            // Remove scheduled_at if we added it
            if (Schema::hasColumn('blog_posts', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
        });
    }
};