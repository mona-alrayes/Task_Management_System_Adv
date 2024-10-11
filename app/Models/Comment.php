<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        'comment'
    ];

    /**
     * Get the parent model (task or any other model) that this comment belongs to.
     * 
     * This method refers to the polymorphic relationship, allowing a comment 
     * to be associated with more than one type of model (e.g., Task, Post, etc.).
     * 
     * In a polymorphic relationship, the `comments` table contains two columns:
     * `commentable_id` (the ID of the related model) and `commentable_type` 
     * (the class name of the related model, such as `App\Models\Task`).
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}
