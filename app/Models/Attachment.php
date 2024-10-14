<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_name',
        'file_path',
        'mime_type',
        'attachable_id',
        'attachable_type',
    ];

    // the polymorphic relationship
    public function attachable()
    {
        return $this->morphTo();
    }
}
