<?php

namespace App\Models;

use App\helpers\AppHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Compus extends Model
{
    use HasFactory;

    protected $casts = [
        'telegram' => 'array',
        'phone' => 'array'
    ];

    protected $appends = ['image_url', 'admission_image_url'];

    public function getImageUrlAttribute()
    {
        if (!empty($this->image)) {
            return asset('uploads/compus/' . rawurlencode($this->image));
        } else {
            return null;
        }
    }

    public function getAdmissionImageUrlAttribute()
    {
        if (!empty($this->admission_image)) {
            return asset('uploads/compus/' . rawurlencode($this->admission_image));
        } else {
            return null;
        }
    }

    public function getNameAttribute($name)
    {
        if (strpos(url()->current(), '/admin')) {
            return $name;
        }
        return $this->translations->where('key', 'name')->first()->value ?? $name;
    }

    public function getDescriptionAttribute($description)
    {
        if (strpos(url()->current(), '/admin')) {
            return $description;
        }
        return $this->translations->where('key', 'description')->first()->value ?? $description;
    }

    public function getAddressAttribute($address)
    {
        if (strpos(url()->current(), '/admin')) {
            return $address;
        }
        return $this->translations->where('key', 'address')->first()->value ?? $address;
    }

    // public function getImageUrlAttribute()
    // {
    //     if (!empty($this->image)) {
    //         $image_url = asset('uploads/compus/' . rawurlencode($this->image));
    //     } else {
    //         $image_url = null;
    //     }
    //     return $image_url;
    // }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->hasMany(Department::class, 'compus_id');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                if (strpos(url()->current(), '/api')) {
                    return $query->where('locale', App::getLocale());
                } else {
                    return $query->where('locale', AppHelper::default_lang());
                }
            }]);
        });
    }
}
