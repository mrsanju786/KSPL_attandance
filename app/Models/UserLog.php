<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    public function user()
    {
        return $this->belongsTo(UserLog::class,'user_id','id');
    }
}
