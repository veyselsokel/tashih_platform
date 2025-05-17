<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BlogPostGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_post_id',
        'image_path',
        'caption', // Resim alt yazısı
        'alt_text', // Alt metin (SEO ve erişilebilirlik için)
        'order',    // Sıralama için
    ];

    /**
     * Bu galeri öğesinin ait olduğu blog yazısını döndürür.
     */
    public function blogPost()
    {
        return $this->belongsTo(BlogPost::class);
    }

    /**
     * Galeri görselinin tam URL'sini döndürür.
     *
     * @return string|null
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::url($this->image_path);
        }
        return null; // Veya varsayılan bir görsel URL'si
    }

    /**
     * Model silinirken ilişkili dosyayı diskten siler.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($galleryImage) {
            if ($galleryImage->image_path && Storage::disk('public')->exists($galleryImage->image_path)) {
                Storage::disk('public')->delete($galleryImage->image_path);
            }
        });
    }
}
