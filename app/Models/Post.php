<?php

namespace App\Models;

use FilamentCurator\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_published',
        'image',
        'category_id',
    ];

    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function featuredImage(): HasOne
    {
        return $this->hasOne(Media::class, 'id', 'featured_image');
    }
}
