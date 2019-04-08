<?php

namespace Slimbug\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * @property integer                 id
 * @property string                  body
 * @property integer                 bug_id
 * @property integer                 user_id
 * @property \Slimbug\Models\User    user
 * @property \Slimbug\Models\Bug     bug
 * @property \Carbon\Carbon          created_at
 * @property \Carbon\Carbon          update_at
 */
class Comment extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'body',
        'user_id',
        'bug_id',
    ];

    /********************
     *  Relationships
     ********************/

    public function bug()
    {
        return $this->belongsTo(Bug::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
