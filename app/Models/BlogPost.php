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

    // CRITICAL: 'status' is REMOVED from fillable.
    // 'is_published' and 'is_draft' are included as they are actual DB columns
    // and the controller prepares them for mass assignment.
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
        'is_published',   // Actual database column
        'is_draft',       // Actual database column
        // 'formatting_new' is not included as it's not handled by controller or Vue
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'formatting' => 'json',
        'is_published' => 'boolean', // Cast to boolean
        'is_draft' => 'boolean',     // Cast to boolean
    ];

    // CRITICAL: 'status' is REMOVED from default attributes.
    // Defaulting is_draft to true, controller will override based on input.
    protected $attributes = [
        'formatting' => '{
            "font": "Inter",
            "fontSize": "16px",
            "lineHeight": "1.5",
            "textAlign": "left",
            "color": "#333333"
        }',
        'is_draft' => true,
        'is_published' => false,
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
     * Scope a query to only include published blog posts.
     * Uses 'is_published' and 'published_at' DB columns.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', Carbon::now());
    }

    /**
     * Scope a query to only include draft blog posts.
     * Uses 'is_draft' DB column.
     */
    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('is_draft', true);
        // Alternatively, if a draft is anything not published:
        // return $query->where('is_published', false);
    }

    /**
     * Scope a query to only include posts by a specific user.
     */
    public function scopeByUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to only include posts that are scheduled and due for publication.
     * These are posts that are not yet published but their scheduled_at time has arrived.
     */
    public function scopeScheduledAndDue(Builder $query): Builder
    {
        return $query->where('is_published', false) // Not yet published
                     // ->where('is_draft', true)    // Optionally, ensure it's still marked as a draft
                     ->whereNotNull('scheduled_at')
                     ->where('scheduled_at', '<=', Carbon::now());
    }

    /**
     * Marks the post as a draft.
     * Note: The controller typically handles setting these before saving.
     * This method is for direct model manipulation if needed, call $model->save() afterwards.
     */
    public function markAsDraft(): self
    {
        $this->is_published = false;
        $this->is_draft = true;
        $this->published_at = null;
        // $this->scheduled_at = null; // Optionally clear schedule
        return $this;
    }

    /**
     * Marks the post as published.
     * Note: The controller typically handles setting these before saving.
     * This method is for direct model manipulation if needed, call $model->save() afterwards.
     */
    public function markAsPublished(): self
    {
        // This method assumes immediate publishing if called directly.
        // The controller and scheduled task handle the nuances of scheduled_at.
        $this->is_published = true;
        $this->is_draft = false;
        // Set published_at only if it's not already set (to preserve original publish date on updates)
        // or if it was previously a draft.
        if ($this->published_at === null || !$this->getOriginal('is_published')) {
            $this->published_at = Carbon::now();
        }
        $this->scheduled_at = null; // Clear schedule once published
        return $this;
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
