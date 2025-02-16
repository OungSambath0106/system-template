<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;
use App\helpers\AppHelper;

class Menu extends Model
{
    use HasFactory;

    protected $appends = ['icon_url', 'image_url'];

    protected $guarded = [];

    public function getIconUrlAttribute()
    {
        if (!empty($this->icon)) {
            return asset('uploads/menus/' . rawurlencode($this->icon));
        } else {
            return null;
        }
    }

    public function getImageUrlAttribute()
    {
        if (!empty($this->image)) {
            return asset('uploads/menus/' . rawurlencode($this->image));
        } else {
            return null;
        }
    }

    public function getTitleAttribute($title)
    {
        if (strpos(url()->current(), '/admin')) {
            return $title;
        }
        return $this->translations->where('key', 'title')->first()->value ?? $title;
    }

    public function getDescriptionAttribute($description)
    {
        if (strpos(url()->current(), '/admin')) {
            return $description;
        }
        return $this->translations->where('key', 'description')->first()->value ?? $description;
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
