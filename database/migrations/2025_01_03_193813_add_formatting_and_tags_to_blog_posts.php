<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'formatting')) {
                $table->json('formatting')->nullable()->after('content');
            }
            if (!Schema::hasColumn('blog_posts', 'tags')) {
                $table->json('tags')->nullable()->after('meta_description');
            }
        });
    }

    public function down()
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['formatting', 'tags']);
        });
    }
};