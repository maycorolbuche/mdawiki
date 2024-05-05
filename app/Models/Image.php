<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $table = 'images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'url',
        'slug',
        'path',
        'width',
        'height',
        'mime',
        'size',
        'updated_by',
    ];

    public function getFullUrlAttribute($value)
    {
        if ($this->attributes['path']) {
            return asset('storage/' . $this->attributes['path']);
        } else {
            return null;
        }
    }
}
