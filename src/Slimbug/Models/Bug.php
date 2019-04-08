<?php

namespace Slimbug\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * @property integer                                  id
 * @property string                                   slug
 * @property string                                   title
 * @property string                                   description
 * @property string                                   body
 * @property integer                                  user_id
 * @property \Slimbug\Models\User                     user
 * @property \Illuminate\Database\Eloquent\Collection comments
 * @property \Carbon\Carbon                           created_at
 * @property \Carbon\Carbon                           update_at
 */
class Bug extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'title',
        'description',
        'body',
        'user_id',
    ];

    public function setSlugAttribute($value)
    {
        $index = 0;
        $slug = $value;
        while (self::newQuery()
            ->where('slug', $slug)
            ->where('id', '!=', $this->id)
            ->exists()) {
            $slug = $value . '-' . ++$index;
        }

        return $this->attributes['slug'] = $slug;
    }

    /********************
     *  Relationships
     ********************/

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create favourites relationship with users
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favourites()
    {
        return $this->belongsToMany(User::class, 'user_favourite');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Check if given user has favourited this bug
     *
     * @param null $id
     *
     * @return bool
     */
    public function isFavouritedByUser($id = null)
    {
        if (is_null($id)) {
            return false;
        }

        if ($id instanceof self) {
            $id = $id->id;
        }

        return $this->newBaseQueryBuilder()
            ->from('user_favourite')
            ->where('user_id', $id)
            ->where('bug_id', $this->id)
            ->exists();
    }
}
