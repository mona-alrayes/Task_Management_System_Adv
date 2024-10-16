<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    // Define the fillable attributes
    protected $fillable = [
        'exception_type',
        'message',
        'trace',
        'file',
        'line',
        'url',
        'method',
        'input',
    ];

    // If you have timestamps in your database, you can enable this
    public $timestamps = true;
}
