<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

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
        'published_at',
        'user_id'
    ];

    protected $casts = [
        'formatting' => 'array',
        'tags' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime'
    ];

    // Yayınlanmış blog yazılarını getiren scope
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                    ->whereNotNull('published_at')
                    ->orderBy('published_at', 'desc');
    }

    // Taslak blog yazılarını getiren scope
    public function scopeDrafts($query)
    {
        return $query->where('is_published', false)
                    ->orWhereNull('published_at')
                    ->orderBy('created_at', 'desc');
    }

    protected $appends = ['featured_image_url'];

    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return Storage::url($this->featured_image);
        }
        return null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}