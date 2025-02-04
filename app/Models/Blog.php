<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'meta_description',
        'tags',
        'is_published',
        'is_draft',
        'user_id'
    ];
    
    protected $casts = [
        'tags' => 'array',
        'is_published' => 'boolean',
        'is_draft' => 'boolean'
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}