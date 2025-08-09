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
        'image',
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
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::url($this->image);
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
            if ($galleryImage->image && Storage::disk('public')->exists($galleryImage->image)) {
                Storage::disk('public')->delete($galleryImage->image);
            }
        });
    }
}
