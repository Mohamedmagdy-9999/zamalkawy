<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $hidden = ['created_at','updated_at','name_ar','name_en','governorate_id'];
    protected $appends = ['name','governorate_name'];

    public function getNameAttribute()
    {
        $locale = app()->getLocale();

        return $locale == 'ar'
            ? $this->name_ar
            : $this->name_en;
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class,'governorate_id');
    }

    public function getGovernorateNameAttribute()
    {
         return $this->governorate ? $this->governorate->name : null;
    }
}
