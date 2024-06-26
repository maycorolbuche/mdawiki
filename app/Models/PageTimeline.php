<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTimeline extends Model
{
    use HasFactory;

    protected $table = 'page_timeline';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_id',
        'slug',
        'type',
        'description',
        'description_html',
        'start_day',
        'start_month',
        'start_year',
        'end_day',
        'end_month',
        'end_year',
        'sequence',
    ];
}
