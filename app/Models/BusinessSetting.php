<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessSetting extends Model
{
    use HasFactory;

    protected $appends = ['web_header_logo_url', 'help_desk_banner_url'];

    protected $guarded = ['id'];

    public function getValueAttribute($value)
    {
        if (strpos(url()->current(), '/admin')) {
            return $value;
        }
        return $this->translations[0]->value ?? $value;
    }

    public function getWebHeaderLogoUrlAttribute()
    {
        if (!empty($this->web_header_logo)) {
            $image_url = asset('uploads/business_settings/' . rawurlencode($this->web_header_logo));
        } else {
            $image_url = null;
        }
        return $image_url;
    }

    public function getHelpDeskBannerUrlAttribute()
    {
        if (!empty($this->help_desk_banner)) {
            $image_url = asset('uploads/business_settings/' . rawurlencode($this->help_desk_banner));
        } else {
            $image_url = null;
        }
        return $image_url;
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
                    return $query->where('locale', session('locale'));
                }
            }]);
        });
    }
}
