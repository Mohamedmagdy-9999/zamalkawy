<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];

    protected $appends = ['image_url', 'likes_count', 'comments_count', 'is_liked','user_name','user_image_url'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function getUserNameAttribute()
    {
         return $this->user ? $this->user->first_name .' '. $this->user->last_name : null;
    }

    public function getUserImageUrlAttribute()
    {
         return $this->user ? $this->user->image_url : null;
    }

    public function comments()
    {
        return $this->hasMany(Comment::class,'post_id');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class,'post_id');
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('posts/' . $this->image) : null;
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    public function getIsLikedAttribute()
    {
        $userId = auth()->guard('api_users')->id();
        if (!$userId) return false;

        return $this->likes()->where('user_id', $userId)->exists();
    }
}
