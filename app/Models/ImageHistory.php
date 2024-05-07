<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ImageHistory extends Model
{
    use HasFactory;

    protected $table = 'image_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image_id',
        'restore_id',
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


    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    public function getUpdatedAtFormattedAttribute()
    {
        return Carbon::parse($this->updated_at)->format('d/m/Y H:i:s');
    }

    public function getFullUrlAttribute()
    {
        if ($this->attributes['path']) {
            return asset('storage/' . $this->attributes['path']);
        } else {
            return null;
        }
    }
}
