<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'assigned_to',

    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Get all comments for the model (Task, Post, etc.) using a polymorphic relationship.
     * 
     * This method defines a one-to-many polymorphic relationship, allowing the model 
     * to have multiple comments. The comments are stored in a shared `comments` table 
     * and can be associated with various models (e.g., Task, Post) through the 
     * `commentable_type` and `commentable_id` fields.
     * 
     * The `morphMany` method indicates that this model can have multiple comments, 
     * and it retrieves all comments associated with this specific model instance.
     * 
     * Example usage:
     * $task = Task::find(1);
     * $comments = $task->comments;
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
