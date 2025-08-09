<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'meta_description',
        'tags',
        'featured_image',
        'published_at',
        'scheduled_at',
        'formatting',
        'status',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'formatting' => 'json',
    ];

    protected $attributes = [
        'formatting' => '{
            "font": "Inter",
            "fontSize": "16px",
            "lineHeight": "1.5",
            "textAlign": "left",
            "color": "#333333"
        }',
    ];

    /**
     * Get the user that owns the blog post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the gallery images for the blog post.
     */
    public function gallery()
    {
        // Ensure BlogPostGallery model exists and is correctly namespaced
        return $this->hasMany(BlogPostGallery::class);
    }

    /**
     * Get the categories for the blog post.
     */
    public function categories()
    {
        // Ensure Category model exists and pivot table 'blog_post_category' is correct
        return $this->belongsToMany(Category::class, 'blog_post_category');
    }

    /**
     * Get the full URL of the featured image.
     */
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image && Storage::disk('public')->exists($this->featured_image)) {
            return Storage::url($this->featured_image);
        }
        return null; // Or a default placeholder image URL
    }

    /**
     * Get the content formatted with styles from the 'formatting' JSON attribute.
     */
    public function getFormattedContentAttribute(): string
    {
        $style = "";
        $formattingData = $this->formatting; // Already an array/object due to casts

        // Check if formattingData is an array (it should be due to 'json' cast)
        if (is_array($formattingData)) {
            $style .= isset($formattingData['font']) ? "font-family: \"" . htmlspecialchars($formattingData['font'], ENT_QUOTES) . "\";" : "";
            $style .= isset($formattingData['fontSize']) ? "font-size: " . htmlspecialchars($formattingData['fontSize'], ENT_QUOTES) . ";" : "";
            $style .= isset($formattingData['lineHeight']) ? "line-height: " . htmlspecialchars($formattingData['lineHeight'], ENT_QUOTES) . ";" : "";
            $style .= isset($formattingData['textAlign']) ? "text-align: " . htmlspecialchars($formattingData['textAlign'], ENT_QUOTES) . ";" : "";
            $style .= isset($formattingData['color']) ? "color: " . htmlspecialchars($formattingData['color'], ENT_QUOTES) . ";" : "";
        }
        // Note: 'json' cast usually converts to associative array, not object.
        // If it can be an object, the elseif block for objects can be kept.

        $htmlContent = nl2br(e($this->content)); // Escape HTML and convert newlines
        return "<div style='{$style}'>{$htmlContent}</div>";
    }

    /**
     * Generate a unique slug for the blog post.
     * If a slug exists, appends a counter (-1, -2, etc.).
     */
    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        if (empty($slug)) { // Handle empty titles that result in empty slugs
            $slug = 'post-' . Str::random(8);
        }
        $originalSlug = $slug;
        $count = 1;

        // Build the initial query to check for slug existence
        $query = static::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Loop until a unique slug is found
        while (static::where('slug', $slug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }
        return $slug;
    }

    

    /**
     * Scope a query to only include posts by a specific user.
     */
    public function scopeByUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('scheduled_at')
                           ->orWhere('scheduled_at', '<=', Carbon::now());
                     });
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include posts that are scheduled and due for publication.
     */
    public function scopeScheduledAndDue(Builder $query): Builder
    {
        return $query->where('status', 'draft')
                     ->whereNotNull('scheduled_at')
                     ->where('scheduled_at', '<=', Carbon::now());
    }

    /**
     * Publish the blog post.
     */
    public function publish(): bool
    {
        $this->status = 'published';
        $this->published_at = Carbon::now();
        
        // If scheduled for future and being published now, clear scheduled_at
        if ($this->scheduled_at && $this->scheduled_at->isFuture()) {
            $this->scheduled_at = null;
        }
        
        return $this->save();
    }

    /**
     * Unpublish the blog post (make it draft).
     */
    public function unpublish(): bool
    {
        $this->status = 'draft';
        $this->published_at = null;
        return $this->save();
    }

    /**
     * Save as draft.
     */
    public function saveDraft(): bool
    {
        $this->status = 'draft';
        return $this->save();
    }

    /**
     * Check if the post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               ($this->scheduled_at === null || $this->scheduled_at->isPast());
    }

    /**
     * Check if the post is scheduled for future publication.
     */
    public function isScheduled(): bool
    {
        return $this->scheduled_at !== null && $this->scheduled_at->isFuture();
    }

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($blogPost) {
            // Delete featured image from storage
            if ($blogPost->featured_image && Storage::disk('public')->exists($blogPost->featured_image)) {
                Storage::disk('public')->delete($blogPost->featured_image);
            }

            // Delete gallery images (files and records)
            // This relies on BlogPostGallery model having its own deleting event for file cleanup if needed.
            if (method_exists($blogPost, 'gallery')) {
                $blogPost->gallery()->each(function ($galleryImage) {
                    $galleryImage->delete();
                });
            }

            // Detach categories from pivot table
            if (method_exists($blogPost, 'categories')) {
                $blogPost->categories()->detach();
            }
        });
    }
}
