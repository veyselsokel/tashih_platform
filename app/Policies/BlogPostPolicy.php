<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogPostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any blog posts.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view the list of posts
        return true;
    }

    /**
     * Determine whether the user can view the blog post.
     */
    public function view(?User $user, BlogPost $blogPost): bool
    {
        // Published posts can be viewed by anyone
        if ($blogPost->status === 'published') {
            return true;
        }

        // Drafts can only be viewed by their authors or admins
        return $user && ($user->id === $blogPost->user_id || $user->is_admin);
    }

    /**
     * Determine whether the user can create blog posts.
     */
    public function create(User $user): bool
    {
        // Only authenticated users can create posts
        return true;
    }

    /**
     * Determine whether the user can update the blog post.
     */
    public function update(User $user, BlogPost $blogPost): bool
    {
        // Users can edit their own posts or if they're admin
        return $user->id === $blogPost->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can delete the blog post.
     */
    public function delete(User $user, BlogPost $blogPost): bool
    {
        // Users can delete their own posts or if they're admin
        return $user->id === $blogPost->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can restore the blog post.
     */
    public function restore(User $user, BlogPost $blogPost): bool
    {
        // Only admins can restore deleted posts
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the blog post.
     */
    public function forceDelete(User $user, BlogPost $blogPost): bool
    {
        // Only admins can permanently delete posts
        return $user->is_admin;
    }

    /**
     * Determine whether the user can publish the blog post.
     */
    public function publish(User $user, BlogPost $blogPost): bool
    {
        // Users can publish their own posts or if they're admin
        return $user->id === $blogPost->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can unpublish the blog post.
     */
    public function unpublish(User $user, BlogPost $blogPost): bool
    {
        // Users can unpublish their own posts or if they're admin
        return $user->id === $blogPost->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can manage post comments.
     */
    public function manageComments(User $user, BlogPost $blogPost): bool
    {
        // Post authors and admins can manage comments
        return $user->id === $blogPost->user_id || $user->is_admin;
    }
}