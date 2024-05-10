<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $table = 'pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'name',
        'slug',
        'content_editor',
        'content_preview',
        'content_text',
        'content_markdown',
        'content_pdf',
        'pending_update',
        'updated_by',
    ];
}
