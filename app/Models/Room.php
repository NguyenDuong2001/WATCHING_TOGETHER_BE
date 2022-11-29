<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
       'message_end',
        'user'
    ] ;

    public function getMessageEndAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function getUserAttribute()
    {
        return $this->user()->first();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('Published', function (Builder $builder) {
            $builder->where('user_id', '!=', null);
        });
    }
}
