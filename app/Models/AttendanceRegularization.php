<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRegularization extends Model
{
    //

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function typeOfRegularization()
    {
        return $this->belongsTo(TypeOfRegularization::class, 'regularization_id','id');
    }
}
