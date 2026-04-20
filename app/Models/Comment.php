<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $guarded = [];
    protected $hidden = ['user_id','updated_at'];
    protected $appends = ['user_name','user_image_url'];

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

    public function post()
    {
        return $this->belongsTo(Post::class,'post_id');
    }
}
