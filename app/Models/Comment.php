<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'object_id',
        'object_type'
    ];

    protected $hidden = [
        'user_id',
        'object_id',
        'object_type'
    ];

    protected $appends = [
        'user',
//        'replies',
        'is_author'
    ];

    public function getRepliesAttribute()
    {
        return $this->replies()->get();
    }

    public function getUserAttribute()
    {
        return $this->user()->first();
    }

    public function getMovieAttribute()
    {
        return $this->movie()->first();
    }

    public function getIsAuthorAttribute()
    {
        return $this->user_id == Auth::user()?->id;
    }

    public function user() {
        return $this->belongsTo(User::class)->select('id','name', 'email');
    }

    public function commentable()
    {
        return $this->morphTo(__FUNCTION__, 'object_type', 'object_id');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }
}
