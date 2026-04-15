<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable,SoftDeletes;

    protected $guarded = [];
    protected $appends = ['image_url','country_name','governorate_name','area_name','gender_name','club_name','profile_completion',
    'missing_fields'];
    protected $hidden = [
        'password',
        'remember_token',
        'test',
    ];
    
    protected $dates = ['deleted_at'];

    // ✅ مطلوب من JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // ✅ مطلوب من JWT
    public function getJWTCustomClaims()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image_url,
            'country_name' => $this->country ? $this->country->name_ar : null,
            'governorate_name' => $this->governorate ? $this->governorate->name_ar : null,
            'area_name' => $this->area ? $this->area->name_ar : null,
            'gender_name' => $this->gender ? $this->gender->name_ar : null,
            'club_name' => $this->club ? $this->club->name_ar : null,
            
        ];
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('users/' . $this->image);
        }
        return null;
    }

    public function country()
    {
        return $this->belongsTo(Country::class,'country_id');
    }

    public function getCountryNameAttribute()
    {
        return $this->country ? $this->country->name_ar : null;
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class,'governorate_id');
    }

    public function getGovernorateNameAttribute()
    {
        return $this->governorate ? $this->governorate->name_ar : null;
    }

    public function area()
    {
        return $this->belongsTo(Area::class,'area_id');
    }

    public function getAreaNameAttribute()
    {
        return $this->area ? $this->area->name_ar : null;
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class,'gender_id');
    }

    public function getGenderNameAttribute()
    {
        return $this->gender ? $this->gender->name_ar : null;
    }

    public function club()
    {
        return $this->belongsTo(Club::class,'club_id');
    }

    public function getClubNameAttribute()
    {
        return $this->club ? $this->club->name_ar : null;
    }

    public function getProfileCompletionAttribute()
    {
        $fields = [
            'first_name',
            'last_name',
            'phone',
            'email',
            'image',
            'birthdate',
            'gender_id',
            'country_id',
            'governorate_id',
            'area_id',
        ];

        $filled = 0;

        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $filled++;
            }
        }

        return round(($filled / count($fields)) * 100);
    }

    public function getMissingFieldsAttribute()
    {
        $fields = [
            'first_name' => 'الاسم الأول',
            'last_name' => 'الاسم الأخير',
            'phone' => 'رقم الهاتف',
            'email' => 'البريد الإلكتروني',
            'image' => 'الصورة',
            'birthdate' => 'تاريخ الميلاد',
            'gender_id' => 'النوع',
            'country_id' => 'الدولة',
            'governorate_id' => 'المحافظة',
            'area_id' => 'المنطقة',
        ];

        return collect($fields)->filter(function ($label, $field) {
            return empty($this->$field);
        })->values();
    }

    
}
