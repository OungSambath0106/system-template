<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;
use App\helpers\AppHelper;

class News extends Model
{
    use HasFactory;

    protected $appends = ['thumbnail_url'];

    protected $guarded = [];

    public function getThumbnailUrlAttribute()
    {
        if (!empty($this->thumbnail)) {
            $thumbnail_url = asset('uploads/News/' . rawurlencode($this->thumbnail));
        } else {
            $thumbnail_url = null;
        }
        return $thumbnail_url;
    }


    public function getTitleAttribute($title)
    {
        if (strpos(url()->current(), '/admin')) {
            return $title;
        }
        return $this->translations->where('key', 'title')->first()->value ?? $title;
    }

    public function getContentAttribute($content)
    {
        if (strpos(url()->current(), '/admin')) {
            return $content;
        }
        return $this->translations->where('key', 'content')->first()->value ?? $content;
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
