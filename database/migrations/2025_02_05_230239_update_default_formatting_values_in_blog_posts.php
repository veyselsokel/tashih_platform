<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Varsayılan format değerlerini ayarla
        DB::table('blog_posts')->whereNull('formatting')->orWhere('formatting', '')->update([
            'formatting' => json_encode([
                'font' => 'Arial, sans-serif',
                'fontSize' => '16px',
                'lineHeight' => '1.5',
                'textAlign' => 'left',
                'color' => '#000000'
            ])
        ]);
    }

    public function down()
    {
        // Herhangi bir değişikliği geri almaya gerek yok
        return;
    }
};