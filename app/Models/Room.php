<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
    ];

    protected $hidden = [
        'user_id'
    ];

    protected $appends = [
       'message_end'
    ] ;

    public function getMessageEndAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
