<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;
    
    protected $fillable = ['author_id','assignee_id','title','description','status','due_date'];

    public function author()   { return $this->belongsTo(User::class, 'author_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assignee_id'); }
    public function comments() { return $this->hasMany(Comment::class); }
}
