<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PageHistory extends Model
{
    use HasFactory;

    protected $table = 'page_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_id',
        'restore_id',
        'title',
        'name',
        'slug',
        'content_editor',
        'content_preview',
        'content_text',
        'content_markdown',
        'content_pdf',
        'categories',
        'pending_update',
        'updated_by',
    ];

    protected $casts = [
        'categories' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    public function getUpdatedAtFormattedAttribute()
    {
        return Carbon::parse($this->updated_at)->format('d/m/Y H:i:s');
    }
}
