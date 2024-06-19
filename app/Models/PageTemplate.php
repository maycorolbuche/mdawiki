<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTemplate extends Model
{
    use HasFactory;

    protected $table = 'page_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_id',
        'slug',
        'template',
        'data',
        'sequence',
    ];
    protected $casts = [
        'data' => 'array',
    ];
}
