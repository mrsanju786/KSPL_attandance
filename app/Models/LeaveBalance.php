<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
		'emp_id',
		'user_id',
		'leave_balance',
        'paid_leaves',
        'casual_leaves',
        'sick_leaves',
        'assigned_leaves',
		'month',
        'year',
		
	];

    public function user()
    {
        return $this->belongsTo(User::class,  'user_id','id');
    }
}
