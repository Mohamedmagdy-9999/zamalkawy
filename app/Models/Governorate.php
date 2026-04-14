<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    use HasFactory;
    protected $hidden = ['created_at','updated_at','name_ar','name_en','country_id'];
    protected $appends = ['name','country_name'];

    public function getNameAttribute()
    {
        $locale = app()->getLocale();

        return $locale == 'ar'
            ? $this->name_ar
            : $this->name_en;
    }

    public function country()
    {
        return $this->belongsTo(Country::class,'country_id');
    }

    public function getCountryNameAttribute()
    {
         return $this->country ? $this->country->name : null;
    }

    public function areas()
    {
        return $this->hasMany(Area::class,'governorate_id');
    }

}
