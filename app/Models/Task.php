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

    public static function blockedTasks()
    {
        return self::where('status', 'blocked')->get();
    }
    
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function user(){
        return $this->belongsTo(User::class,'assigned_to');
    }

    //The tasks that this task depends on.
    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id');
    }
    //The tasks that are waiting on this task.
    public function dependentTasks()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id');
    }

     //the polymorphic relationship with attachments
     public function attachments()
     {
         return $this->morphMany(Attachment::class, 'attachable');
     }

     protected static function booted()
    {
        static::updating(function ($task) {
            if ($task->isDirty('status')) {
                // Log the status update in the task_status_updates table
                TaskStatusUpdate::create([
                    'task_id' => $task->id,
                    'old_status' => $task->getOriginal('status'),
                    'new_status' => $task->status,
                    'changed_at' => now(),
                ]);
            }
        });
        
        static::updating(function ($task) {
            $changes = $task->getDirty();

            foreach ($changes as $field => $newValue) {
                TaskLog::create([
                    'task_id' => $task->id,
                    'field_changed' => $field,
                    'old_value' => $task->getOriginal($field),
                    'new_value' => $newValue,
                    'changed_at' => now(),
                ]);
            }
        });
    }
   
}
