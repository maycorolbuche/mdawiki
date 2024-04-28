<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailQueue extends Model
{
    use HasFactory;

    protected $table = 'email_queue';

    protected $fillable = [
        'user_id',
        'recipient_email',
        'subject',
        'content',
        'scheduled_at',
        'status',
        'error_message',
    ];
}
