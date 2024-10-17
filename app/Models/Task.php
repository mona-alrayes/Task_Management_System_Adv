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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Retrieve tasks with a status of 'blocked'.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function blockedTasks()
    {
        return self::where('status', 'blocked')->get();
    }

    /**
     * Get all comments for the task (polymorphic relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get the user assigned to this task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the tasks that this task depends on (many-to-many self-relation).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id');
    }

    /**
     * Get the tasks that are waiting on this task to be completed (many-to-many self-relation).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function dependentTasks()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id');
    }

    /**
     * Get all attachments for the task (polymorphic relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Boot the model and add global event listeners for logging task updates.
     *
     * Logs status changes and general field changes in separate log tables.
     *
     * @return void
     */
    protected static function booted()
    {
        static::updating(function ($task) {
            // Log status changes in the task_status_updates table
            if ($task->isDirty('status')) {
                TaskStatusUpdate::create([
                    'task_id' => $task->id,
                    'old_status' => $task->getOriginal('status')?? null,
                    'new_status' => $task->status,
                    'changed_at' => now(),
                ]);
            }
        });
        
        static::updating(function ($task) {
            // Log any field changes in the task_logs table
            $changes = $task->getDirty();

            foreach ($changes as $field => $newValue) {
                TaskLog::create([
                    'task_id' => $task->id,
                    'field_changed' => $field,
                    'old_value' => $task->getOriginal($field)?? null,
                    'new_value' => $newValue,
                    'changed_at' => now(),
                ]);
            }
        });
    }
}
