<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
     protected $fillable = ['name', 'description', 'owner_id', 'bg_color'];

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function members() {
        return $this->belongsToMany(User::class, 'board_members')
                    ->withPivot('role')->withTimestamps();
    }
    public function columns() {
        return $this->hasMany(Column::class)->orderBy('position');
    }
    public function isMember(User $user): bool {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
