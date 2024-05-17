<?php

namespace Yonidebleeker\UpStats\Http\Models;

use Illuminate\Database\Eloquent\Model;

interface UpStatsUser
{
    public function canAccessStats(): bool;
}
