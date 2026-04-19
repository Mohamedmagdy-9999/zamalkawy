<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['image_url','title','category_name','desc','views_count','is_liked'];

    public function getImageUrlAttribute()
    {
        return asset('blog/' . $this->image);
    }

    public function getTitleAttribute()
    {
        $locale = app()->getLocale();

        return $locale == 'ar'
            ? $this->title_ar
            : $this->title_en;
    }



    public function getDescAttribute()
    {
        $locale = app()->getLocale();

        return $locale == 'ar'
            ? $this->desc_ar
            : $this->desc_en;
    }


    public function category()
    {
        return $this->belongsTo(Category::class , 'category_id');
    }

    public function getCategoryNameAttribute()
    {
         return $this->category ? $this->category->name : null;
    }

    public function views()
    {
        return $this->hasMany(BlogView::class, 'blog_id');
    }

    public function getViewsCountAttribute()
    {
        return $this->views()->count();
    }

    public function likes()
    {
        return $this->hasMany(BlogLike::class,'blog_id');
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'blog_likes');
    }

    public function isLiked()
    {
        $userId = auth()->guard('api_users')->id();

        if (!$userId) return false;

        return $this->likes()->where('user_id', $userId)->exists();
    }
    public function getIsLikedAttribute()
    {
        return $this->isLiked();
    }
}
