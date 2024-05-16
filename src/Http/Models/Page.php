<?php

namespace Yonidebleeker\UpStats\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Yonidebleeker\UpStats\Http\Models\Pagevisit;

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
