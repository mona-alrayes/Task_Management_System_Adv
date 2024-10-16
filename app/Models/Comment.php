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
     * This defines a polymorphic relationship, allowing the comment to be associated 
     * with different models (e.g., Task) using `commentable_id` and `commentable_type`.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}
