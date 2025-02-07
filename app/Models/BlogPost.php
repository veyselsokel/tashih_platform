<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'formatting',
        'featured_image',
        'meta_description',
        'tags',
        'is_published',
        'is_draft',
        'published_at',
        'user_id'
    ];

    protected $casts = [
        'formatting' => 'json', 
        'tags' => 'array',
        'is_published' => 'boolean',
        'is_draft' => 'boolean',
        'published_at' => 'datetime'
    ];
    
    public function getFormattedContentAttribute()
    {
        $formatting = $this->formatting ?? [];
        return [
            'content' => $this->content,
            'formatting' => [
                'font' => $formatting['font'] ?? 'Arial, sans-serif',
                'fontSize' => $formatting['fontSize'] ?? '16px',
                'lineHeight' => $formatting['lineHeight'] ?? '1.5',
                'textAlign' => $formatting['textAlign'] ?? 'left',
                'color' => $formatting['color'] ?? '#000000',
            ]
        ];
    }

    protected $appends = ['featured_image_url'];

    // Başlık değiştiğinde otomatik slug oluşturma
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if ($post->title) {
                $post->slug = $post->generateUniqueSlug($post->title);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title')) {
                $post->slug = $post->generateUniqueSlug($post->title);
            }
        });
    }

    // Benzersiz slug oluşturma
    protected function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
        
        return $count ? "{$slug}-{$count}" : $slug;
    }

    // Yayınlanmış blog yazılarını getiren scope
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                    ->where('is_draft', false)
                    ->whereNotNull('published_at')
                    ->orderBy('published_at', 'desc');
    }

    // Taslak blog yazılarını getiren scope
    public function scopeDrafts($query)
    {
        return $query->where('is_draft', true)
                    ->orderBy('updated_at', 'desc');
    }

    // Kullanıcının blog yazılarını getiren scope
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Öne çıkan görsel URL'i
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return Storage::url($this->featured_image);
        }
        return null;
    }

    // İlişkiler
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Yardımcı metodlar
    public function saveDraft()
    {
       $this->update([
           'is_draft' => true,
           'is_published' => false,
           'published_at' => null
       ]);
    }

    public function publish()
    {
        $this->update([
            'is_draft' => false,
            'is_published' => true,
            'published_at' => now()
        ]);
    }

    public function unpublish()
    {
        $this->update([
            'is_draft' => false,
            'is_published' => false,
            'published_at' => null
        ]);
    }

    // Küçük resim URL'i (eğer implement edilecekse)
    public function getThumbnailUrl($width = 300, $height = 200)
    {
        if ($this->featured_image) {
            // Burada görsel işleme mantığı implement edilebilir
            return Storage::url($this->featured_image);
        }
        return null;
    }

    public function gallery()
    {
        return $this->hasMany(BlogPostGallery::class)->orderBy('order');
    }
}