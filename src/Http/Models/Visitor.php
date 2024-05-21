<?php

namespace Digitalup\UpStats\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $table = 'visitors';
    protected $fillable = ['cookie', 'source', 'device_type'];

    // Add any additional methods or relationships here
    public function pagevisits()
    {
        return $this->hasMany(Pagevisit::class);
    }
}
