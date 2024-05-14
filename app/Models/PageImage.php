<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageImage extends Model
{
    use HasFactory;

    protected $table = 'page_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_id',
        'slug',
        'image_slug',
        'image_path',
        'image_exists',
        'is_error',
        'sequence',
    ];
}
