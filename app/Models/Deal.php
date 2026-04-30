<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $hidden = ['created_at','updated_at','desc_ar','desc_en','merchant_id'];
    protected $appends = ['desc','merchant_name','image_url'];

    public function getDescAttribute()
    {
        $locale = app()->getLocale();

        return $locale == 'ar'
            ? $this->desc_ar
            : $this->desc_en;
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('deals/' . $this->image);
        }
        return null;
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class,'merchant_id');
    }

    public function getMerchantNameAttribute()
    {
         return $this->merchant ? $this->merchant->name : null;
    }

    
}
