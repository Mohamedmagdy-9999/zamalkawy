<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory;

    protected $hidden = ['created_at','updated_at','name_ar','name_en','merchant_category_id','club_id'];
    protected $appends = ['name','category_name','club_name'];

    public function getNameAttribute()
    {
        $locale = app()->getLocale();

        return $locale == 'ar'
            ? $this->name_ar
            : $this->name_en;
    }

    public function category()
    {
        return $this->belongsTo(MerchantCategory::class,'merchant_category_id');
    }

    public function getCategoryNameAttribute()
    {
         return $this->category ? $this->category->name : null;
    }

    public function club()
    {
        return $this->belongsTo(Club::class,'club_id');
    }

    public function getClubNameAttribute()
    {
         return $this->club ? $this->club->name : null;
    }

    

}
