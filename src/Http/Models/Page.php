<?php

namespace Digitalup\UpStats\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Digitalup\UpStats\Http\Models\Pagevisit;

class Page extends Model
{
    protected $table = 'pages';
    protected $fillable = ['url'];

    // Add any additional methods or relationships here
    public function pagevisits()
    {
        return $this->hasMany(Pagevisit::class);
    }
}
