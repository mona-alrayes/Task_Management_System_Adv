<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    
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

    public $timestamps = true;
}
