<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    use HasFactory;

    protected $table = 'task_logs';

    protected $fillable = [
        'task_name',
        'affected_records_count',
        'start_time',
        'end_time',
    ];
}
