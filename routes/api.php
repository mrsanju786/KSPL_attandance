<?php

// use App\Http\Controllers\Api\Holiday\HolidayController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login', 'Api\Auth\ApiAuthController@login');

Route::post('forgot-password', 'Api\Auth\ApiAuthController@apiFotgotPassword');
Route::middleware('auth:api')->group(function () {
    Route::post('logout', 'Api\Auth\ApiAuthController@logout');
    // Route::post('apiProfile', 'Api\Auth\ApiAuthController@apiChangePassword');

    Route::post('/apiChangePassword', 'Api\Auth\ApiAuthController@apiChangePassword');
    Route::post('/updateProfileImage', 'Api\Auth\ApiAuthController@updateProfileImage');
    Route::get('/getProfile', 'Api\Auth\ApiAuthController@getProfile');

   //notification list
   Route::get('/notification-list', 'Api\Auth\ApiAuthController@notificationList');
    // update profile

    Route::post('/apiHelpDesk', 'Api\Helpdesk\HelpDeskController@store');
    Route::get('/getTicketList/{id?}', 'Api\Helpdesk\HelpDeskController@ticket_list');
    Route::post('/updateTicketStatus', 'Api\Helpdesk\HelpDeskController@updateTicketStatus');

    /**
     * Attendance
     */
    Route::get('area/index', 'Api\Area\ApiAreaController@index');
    Route::post('attendance/apiSaveAttendance', 'Api\Attendance\ApiAttendanceController@apiSaveAttendance');
    Route::get('attendance/getStaffAttendance', 'Api\Attendance\ApiAttendanceController@apiCheckinList');
    Route::post('attendance/{attendance}/approve', 'Api\Attendance\ApiAttendanceController@apiApproveAttendance');
    Route::post('attendance/mark-ot/{user}', 'Api\Attendance\ApiAttendanceController@markOT');
    Route::post('attendance/apiApprovePendingCount', 'Api\Attendance\ApiAttendanceController@getApprovePendingCount');
    Route::post('attendance/apiSaveAttendanceNew', 'Api\Attendance\ApiAttendanceController@apiSaveAttendanceNew');

    Route::post('attendance/tsm-check-in-staff', 'Api\Attendance\ApiAttendanceController@tsmCheckInStaff');
    Route::post('attendance-regularization/attendance-mark-request', 'Api\AttendanceRegularization\AttendanceRegularizationController@attendanceMarkRequest');
    Route::get('attendance-regularization/regularization-type-of-List', 'Api\AttendanceRegularization\AttendanceRegularizationController@regularizationTypeOfList');
    Route::get('attendance-regularization/regularization-list', 'Api\AttendanceRegularization\AttendanceRegularizationController@regularizationList');
    Route::get('attendance-regularization/regularization-staff-list', 'Api\AttendanceRegularization\AttendanceRegularizationController@regularizationStaffList');
    Route::get('attendance-regularization/update-status/{id}/{status}', 'Backend\AttendanceRegularization\AttendanceRegularizationController@updateStatus');

    //get rsm branch 
    Route::get('rsm-branch-list', 'Api\Attendance\ApiAttendanceController@rsmBranchWiseList');
    Route::get('rsm-tsm-obst-list', 'Api\Attendance\ApiAttendanceController@rsmTsmObstList');
    /**
     * Holiday
     */
    Route::get('apiHoliday', 'Api\Holiday\HolidayController@index');
    Route::post('apiHoliday', 'Api\Holiday\HolidayController@store');
    Route::post('apiUpdateHolidayStatus/{holiday}', 'Api\Holiday\HolidayController@updateStatus');

    /**
     * rsm tsm apis
     */
    Route::get('apiGetTsms/{id?}', 'Api\Area\ApiAreaController@tsms_list');
    Route::get('apiGetStaff/{id?}', 'Api\Area\ApiAreaController@staff_list');
    Route::get('SelfList/{id?}', 'Api\Area\ApiAreaController@self_list');
    Route::get('dateAttendance', 'Api\Area\ApiAreaController@date_attendance');

    Route::get('tsm-staff-list', 'Api\Attendance\ApiAttendanceController@tsmstaffList');
    Route::get('only-tsm-staff-list', 'Api\Attendance\ApiAttendanceController@onlyTsmstaffList');
   

    /**
     * leave apis
     */
    Route::post('saveLeave', 'Api\Leave\LeaveController@saveLeave');
    Route::get('leave-staff-list', 'Api\Leave\LeaveController@leaveStaffList');
    Route::post('approve-leave', 'Api\Leave\LeaveController@approveLeave');
    Route::get('leave-list', 'Api\Leave\LeaveController@staffLeaveList');
    
    
     /**
     * od  apis
     */
    Route::post('save-od', 'Api\OutDoor\OutDoorController@applyOutDoor');
    Route::get('od-staff-list', 'Api\OutDoor\OutDoorController@odStaffList');
    Route::post('approve-od', 'Api\OutDoor\OutDoorController@approveOD');
    Route::get('od-list', 'Api\OutDoor\OutDoorController@outDoorList');
    Route::get('od-rsm-list', 'Api\OutDoor\OutDoorController@odRsmList');
    /**
     * cmt apis
     */
    Route::get('all-branch-list', 'Api\Cmt\CmtController@branchList');
    Route::get('all-staff-attendance-list', 'Api\Cmt\CmtController@cmtAttendanceList');
    Route::get('staff-attendance', 'Api\Cmt\CmtController@cmtStaffAttendance');
    Route::post('cmt-checkin-staff', 'Api\Cmt\CmtController@cmtCheckInStaff');
    Route::get('search-staff', 'Api\Cmt\CmtController@filterStaffAttendance');

    Route::get('deployed-branch-lat-long', 'Api\Attendance\ApiAttendanceController@userLatLong');
    
    //holiday list for staff
    Route::get('staff-holiday-list', 'Api\Holiday\HolidayController@userHolidayAttendanceList');
    Route::get('staff-attendance-list', 'Api\Holiday\HolidayController@userAttendanceList');

});

//notice list
Route::get('notice-list', 'Api\Notice\NoticeController@noticeList');
Route::get('notice-read', 'Api\Notice\NoticeController@noticeRead');
//state list
Route::get('state-list', 'Api\Holiday\HolidayController@stateList');
Route::get('holiday-list', 'Api\Holiday\HolidayController@stateHoliday');
/*
Route::get('/helper/{code}', function ($code) {return App\Helpers\Helper::checkingCode($code);});
Route::get('/helper', function () {return App\Helpers\Helper::getInfo();});
Route::get('/write', function () {return App\Helpers\Helper::write();});
*/
Route::get('rsm-list', 'Api\Attendance\ApiAttendanceController@rsmList');
Route::get('leave-balance-list', 'Api\Leave\LeaveController@leaveBalanceList');
// Route::get('tsm-staff-list', 'Api\Attendance\ApiAttendanceController@tsmstaffList');

Route::get('/selfie-status', 'Api\Setting\SettingsController@selfieStatus');

