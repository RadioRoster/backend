<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Show extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
    ];

    public function locked_by()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function moderators()
    {
        return $this->belongsToMany(User::class, 'show_moderators', 'show_id', 'moderator_id')->as('moderators')->withTimestamps();
    }

    public function primary_moderator()
    {
        return $this->belongsToMany(User::class, 'show_moderators', 'show_id', 'moderator_id')->wherePivot('primary', true);
    }
}
