<?php

namespace Slimbug\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * @property string         title
 * @property integer         bug_id
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon update_at
 */
class Tag extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
    ];


    /********************
     *  Relationships
     ********************/

    public function bugs()
    {
        return $this->belongsToMany(Bug::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
