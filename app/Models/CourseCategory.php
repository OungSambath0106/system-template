<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use App\helpers\AppHelper;

class CourseCategory extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['curriculum_image_url', 'assessment_detail_url', 'icon_url'];

    public function getCurriculumImageUrlAttribute()
    {
        if (!empty($this->curriculum_image)) {
            return asset('uploads/Course/' . rawurlencode($this->curriculum_image));
        } else {
            return null;
        }
    }

    public function getAssessmentDetailUrlAttribute()
    {
        if (!empty($this->assessment_detail)) {
            return asset('uploads/Course/' . rawurlencode($this->assessment_detail));
        } else {
            return null;
        }
    }

    public function getIconUrlAttribute()
    {
        if (!empty($this->icon)) {
            return asset('uploads/Course/' . rawurlencode($this->icon));
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
