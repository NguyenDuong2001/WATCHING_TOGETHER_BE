<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reply extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'comment_id'
    ];

    protected $appends = [
        'user',
    ];

    public function getUserAttribute()
    {
        return $this->user()->first();
    }

    public function user() {
        return $this->belongsTo(User::class)->select('id','name', 'email');
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
