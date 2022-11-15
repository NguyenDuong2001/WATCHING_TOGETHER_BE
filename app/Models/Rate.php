<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate',
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
    ];

    public function getUserAttribute()
    {
        return $this->user()->first();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->select('id','name', 'email');
    }

    public function rateable()
    {
        return $this->morphTo(__FUNCTION__, 'object_type', 'object_id');
    }
}
