<?php

namespace Digitalup\UpStats\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Digitalup\UpStats\Http\Models\Page;

class Pagevisit extends Model
{
    protected $table = 'pagevisits';
    protected $fillable = ['page_id', 'visitor_id'];

    // Add any additional methods or relationships here
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }
}
