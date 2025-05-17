<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BlogPost;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PublishScheduledBlogPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes blog posts that are scheduled and due for publication.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Zamanlanmış blog yazılarını kontrol ediliyor...');

        $postsToPublish = BlogPost::where('status', 'draft') // Henüz yayınlanmamış
                                   ->whereNotNull('scheduled_at')
                                   ->where('scheduled_at', '<=', Carbon::now())
                                   ->get();

        if ($postsToPublish->isEmpty()) {
            $this->info('Yayınlanacak zamanlanmış yazı bulunamadı.');
            return 0;
        }

        foreach ($postsToPublish as $post) {
            try {
                $post->status = 'published';
                $post->published_at = $post->scheduled_at; // Yayınlanma tarihini zamanlandığı tarih yap
                // $post->scheduled_at = null; // İsteğe bağlı olarak zamanlamayı temizle
                $post->save();
                $this->info("Yazı yayınlandı: ID {$post->id} - Başlık: {$post->title}");
                Log::info("Zamanlanmış yazı yayınlandı: ID {$post->id} - Başlık: {$post->title}");
            } catch (\Exception $e) {
                $this->error("Yazı yayınlanırken hata oluştu (ID: {$post->id}): " . $e->getMessage());
                Log::error("Zamanlanmış yazı yayınlanırken hata (ID: {$post->id}): " . $e->getMessage());
            }
        }

        $this->info('Zamanlanmış blog yazıları kontrolü tamamlandı.');
        return 0;
    }
}
