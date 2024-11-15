<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeOfRegularization extends Model
{
    //
    protected $fillable = [
		'name',
		'staus',

	];

    public function attendanceregularization()
    {
        return $this->hasMany(AttendanceRegularization::class, 'regularization_id', 'id');
    }
}
