<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTitle extends Model
{
    use HasFactory;

    protected $table = 'page_titles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_id',
        'restore_id',
        'slug',
        'header',
        'header_slug',
        'level',
        'content',
        'sequence',
    ];
}
