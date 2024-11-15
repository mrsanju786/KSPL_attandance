<?php

namespace App\Models;

use App\Models\Role;
use App\Models\User;
use App\Models\AttendanceStatus;
use App\Models\Base\Attendance as BaseAttendance;

class Attendance extends BaseAttendance
{
	protected $fillable = [
		'worker_id',
        'worker_role_id',
		'date',
		'in_time',
		'out_time',
		'work_hour',
		'over_time',
		'late_time',
		'early_out_time',
		'in_location_id',
		'out_location_id',
        'status'
	];

    protected $dates = [
        'date'
    ];

    protected $casts = [
        'date'  => 'date:Y-m-d',
    ];

	public function attendanceStatus(){
        return $this->belongsTo(AttendanceStatus::class,'status','id');
    }
	

	public function attendanceUpdatedBy(){
        return $this->belongsTo(User::class,'status_updated_by','id');
    }

	public function attendanceRole(){
		return $this->hasManyThrough(
            Role::class,
           	User::class,
            'role', 
            'id', 
            'worker_id', 
            'id' 
        );
    }
}
