<?php

namespace App\Models;

use App\Enums\RoleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'sender_id',
        'receiver_id',
        'room_id',
        'user_seen',
        'admin_seen'
    ];

    protected $hidden = [
        'room_id',
        'sender_id',
        'receiver_id'
    ];

    protected $appends = [
        'is_author',
        'sender',
        'receiver'
    ];

    public function getSenderAttribute()
    {
        return $this->sender()->first()->role->name !== RoleType::Customer ? null : $this->sender()->first();
    }

    public function getReceiverAttribute()
    {
        return $this->receiver()->first();
    }

    public function getIsAuthorAttribute()
    {
        if (Auth::user()?->role->name != RoleType::Customer && $this->sender()->first()?->role->name != RoleType::Customer)
        {
            return true;
        }

        return $this->sender()->first()?->id === Auth::user()?->id;
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
