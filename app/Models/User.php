<?php

namespace App\Models;

use Auth;
use App\Models\RsmTsm;
use App\Models\TsmEmp;
use App\Models\Helpdesk;
use App\Models\Designation;
use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\LaratrustUserTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends \App\Models\Base\User implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    // Always remember to put necessary traits inside class after defining them below namespace
    // These traits are used by default for user login authentication
    use LaratrustUserTrait {
        LaratrustUserTrait::can insteadof Authorizable;
        Authorizable::can as authorizableCan;
    }
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        Notifiable,
        HasApiTokens;

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'email_verified_at',
		'password',
		'remember_token',
        'reset_token',
        'reset_token_expiry',
        'image',
        'home_latitude1',
        'home_longitude1',
        'home_latitude2',
        'home_longitude2',
        'role',
        'emp_id',
        'area_id',
        'designation',
        'mobile_number',
        'blood_group',
        'emergency_contact',
	];

	// Function get user image from database
	public function adminlte_image() {

	    $getImage = User::find(Auth::user()->id);
	    $image = asset('uploads/'.$getImage->image);

	    return $image;
    }

    public function adminlte_desc() {
        return 'Hi, Welcome!';
    }

    /**
     * Get all of the areas for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function areas()
    {
        return $this->belongsToMany(Area::class, TsmArea::class, 'tsm_id', 'area_id');
    }

    /**
     * Get the area associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get all of the attendance for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'worker_id', 'id');
    }

    public function attendanceregularization()
    {
        return $this->hasMany(AttendanceRegularization::class, 'user_id', 'id');
    }

    /**
     * The staff that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staff()
    {
        return $this->belongsToMany(User::class, 'tsm_emps', 'tsm_id', 'emp_id');
    }

    /**
     * The staffAttendance that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staffAttendance()
    {
        return $this->belongsToMany(Attendance::class, 'tsm_emps', 'tsm_id', 'emp_id');
    }

     /**
     * Get the area associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tsmEmp()
    {
        return $this->hasOne(TsmEmp::class,'emp_id','id');
    }

    /**
     * Get the rsm associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function rsmTsm()
    {
        return $this->hasOne(RsmTsm::class,'rsm_id' ,'id');
    }

    /**
     * Get the empDesignation associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function empDesignation()
    {
        return $this->hasOne(Designation::class, 'id', 'designation');
    }

    /**
     * Get the helpdesk associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function helpdesks()
    {
        return $this->hasMany(Helpdesk::class,'user_id','id');
    }

     /**
     * Get the empDesignation associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userRole()
    {
        return $this->hasOne(Role::class, 'id', 'role');
    }

    public function userLog()
    {
        return $this->hasOne(UserLog::class, 'id', 'user_id');
    }


}
