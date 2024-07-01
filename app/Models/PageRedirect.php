<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageRedirect extends Model
{
    use HasFactory;

    protected $table = 'page_redirects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_id',
        'slug',
        'page_redirect_slug',
        'page_redirect_title',
        'page_exists',
        'sequence',
    ];

    public function page_to()
    {
        return $this->belongsTo(Page::class,'page_redirect_slug','slug');
    }
}
