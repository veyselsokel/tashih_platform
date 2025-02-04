<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BlogPostGallery extends Model
{
    protected $fillable = ['blog_post_id', 'image', 'caption', 'alt_text', 'order'];
    
    protected $appends = ['image_url'];

    public function blogPost()
    {
        return $this->belongsTo(BlogPost::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        
        return url('storage/' . $this->image);
    }
}