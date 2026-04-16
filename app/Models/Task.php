<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'column_id', 'assigned_to', 'title',
        'description', 'position', 'priority', 'due_date'
    ];
    protected $casts = ['due_date' => 'date', 'is_archived' => 'boolean'];

    public function column() { return $this->belongsTo(Column::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function comments() { return $this->hasMany(Comment::class)->latest(); }
}
