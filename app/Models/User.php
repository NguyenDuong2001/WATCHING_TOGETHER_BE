<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role_id',
        'address',
        'password',
        'country_id',
        'phone_number',
        'date_of_birth',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'media',
        'role_id',
        'password',
        'country_id',
        'remember_token',
    ];

    protected $appends = [
        'role',
        'avatar',
        'country',
    ];

    public function getAvatarAttribute()
    {
        $listAvatars = collect([]);

        $avatars = $this->getMedia('avatar');

        if ($avatars->count() <= 0){
            return ['https://i.pravatar.cc/300?img='.rand(1, 70)];
        }

        foreach ($avatars as $avatar){
            $listAvatars->push($avatar->getFullUrl());
        }

        return $listAvatars;
    }

    public function getCountryAttribute()
    {
        return $this->country()->first();
    }

    public function getRoleAttribute()
    {
        return $this->role()->first();
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
