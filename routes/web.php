<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});



Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/reset_password/{token}/{email}', 'Auth\ResetPasswordController@create');
Route::post('/set_password', 'Auth\ResetPasswordController@setPassword');

Route::get('/employ-not-login-report', 'HomeController@employeeNotLoggedTillTdate')->name('employ-not-login-report');
Route::get('/employ-not-login-one-week-report', 'HomeController@employeeNotLoggedOneWeek')->name('employ-not-login-one-week-report');

/*
|--------------------------------------------------------------------------
| administrator
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin']], function () {

    Route::GET('/users/list/{status?}', 'Backend\Users\UsersController@index')->name('users');
    Route::GET('/users/add', 'Backend\Users\UsersController@add')->name('users.add');
    Route::POST('/users/create', 'Backend\Users\UsersController@create')->name('users.create');
    Route::GET('/users/edit/{id}', 'Backend\Users\UsersController@edit')->name('users.edit');
    Route::POST('/users/update', 'Backend\Users\UsersController@update')->name('users.update');
    Route::GET('/users/delete/{id}', 'Backend\Users\UsersController@delete')->name('users.delete');
    Route::GET('/users/import', 'Backend\Users\UsersController@import')->name('users.import');
    Route::POST('/users/importData', 'Backend\Users\UsersController@importData')->name('users.importData');

    Route::GET('/users/activeUser/{id}', 'Backend\Users\UsersController@activeUser')->name('users.activeUser');
    Route::GET('/users/deactiveUser/{id}', 'Backend\Users\UsersController@deactiveUser')->name('users.deactiveUser');
    Route::GET('/users/LogoutUser/{id}', 'Backend\Users\UsersController@LogoutUser')->name('users.LogoutUser');
    Route::GET('/users/routeAttendance/{id}', 'Backend\Users\UsersController@routeAttendance')->name('users.routeAttendance');
    Route::POST('/users/routeAttendanceUpdate', 'Backend\Users\UsersController@routeAttendanceUpdate')->name('users.routeAttendanceUpdate');
    //add-staff tsm/rsm  dependent area list
    Route::POST('/users/tsm_rsm_area_list', 'Backend\Users\UsersController@tsm_rsm_area_list');

    Route::GET('/settings', 'Backend\Setting\SettingsController@index')->name('settings');
    Route::POST('/settings/update', 'Backend\Setting\SettingsController@update')->name('settings.update');

    //helpdesk    
    Route::GET('/helpdesk', 'Backend\Helpdesk\HelpdeskController@index')->name('helpdesk');

    //holiday    
    Route::GET('/holiday', 'Backend\Holiday\HolidayController@index')->name('holiday');
    Route::GET('/holiday/add', 'Backend\Holiday\HolidayController@add')->name('holiday.add');
    Route::POST('/holiday/create', 'Backend\Holiday\HolidayController@create')->name('holiday.create');
    Route::GET('/holiday/edit/{id}', 'Backend\Holiday\HolidayController@edit')->name('holiday.edit');
    Route::POST('/holiday/update', 'Backend\Holiday\HolidayController@update')->name('holiday.update');
    Route::GET('/holiday/import', 'Backend\Holiday\HolidayController@import')->name('holiday.import');
    Route::POST('/holiday/importData', 'Backend\Holiday\HolidayController@importData')->name('holiday.importData');

    Route::GET('/helpdesk/open/{id}', 'Backend\Helpdesk\HelpdeskController@Open')->name('helpdesk.open');
    Route::GET('/helpdesk/closed/{id}', 'Backend\Helpdesk\HelpdeskController@Closed')->name('helpdesk.closed');
    Route::POST('helpdesk/remark/{id}', 'Backend\Helpdesk\HelpdeskController@Remark')->name('remark');
    //leave management
    Route::GET('/leave', 'Backend\Leave\LeaveController@index')->name('leave');

    //Leave Balance    
    Route::GET('/leavebalance', 'Backend\Leave\LeaveBalanceController@index')->name('leavebalance');
    Route::GET('/leavebalance/add', 'Backend\Leave\LeaveBalanceController@add')->name('leavebalance.add');
    Route::POST('/leavebalance/create', 'Backend\Leave\LeaveBalanceController@create')->name('leavebalance.create');
    Route::GET('/leavebalance/edit/{id}', 'Backend\Leave\LeaveBalanceController@edit')->name('leavebalance.edit');
    Route::POST('/leavebalance/update', 'Backend\Leave\LeaveBalanceController@update')->name('leavebalance.update');
    Route::GET('/leavebalance/import', 'Backend\Leave\LeaveBalanceController@import')->name('leavebalance.import');
    Route::POST('/leavebalance/importData', 'Backend\Leave\LeaveBalanceController@importData')->name('leavebalance.importData');
    Route::GET('/leavebalance/delete/{id}', 'Backend\Leave\LeaveBalanceController@delete')->name('leavebalance.delete');

    //od management
    Route::GET('/od', 'Backend\OutDoor\OutDoorController@index')->name('od');
    // reports
    Route::GET('/reports', 'Backend\Reports\ReportsController@index')->name('reports');
    Route::GET('/horizontal-reports', 'Backend\Reports\ReportsController@horizontal_report')->name('reportsHorizontal');
    Route::GET('/reports-OBST', 'Backend\Reports\ReportsController@obst')->name('reportsObst');
    Route::GET('/reports-DST', 'Backend\Reports\ReportsController@dst')->name('reportsDst');
    Route::GET('/reports-OBA', 'Backend\Reports\ReportsController@boa')->name('reportsBoa');
    Route::get('/export/{date1?}/{date2?}', 'Backend\Reports\ReportsController@export')->name('export');
    Route::get('/export-OBST/{date1?}/{date2?}', 'Backend\Reports\ReportsController@exportOBST')->name('OBST.export');
    Route::get('/export-DST/{date1?}/{date2?}', 'Backend\Reports\ReportsController@exportDST')->name('DST.export');
    Route::get('/export-BOA/{date1?}/{date2?}', 'Backend\Reports\ReportsController@exportBOA')->name('BOA.export');
    Route::get('/export-TSM/{date1?}/{date2?}', 'Backend\Reports\ReportsController@exportTSM')->name('TSM.export');
    Route::get('/export-RSM/{date1?}/{date2?}', 'Backend\Reports\ReportsController@exportRSM')->name('RSM.export');
    Route::get('/export-All/{date1?}/{date2?}', 'Backend\Reports\ReportsController@exportAll')->name('All.export');
    Route::get('/user-never-login', 'Backend\Reports\ReportsController@user_never_login')->name('user_never_login');


    //notice    
    Route::GET('/notice', 'Backend\Notice\NoticeController@index')->name('notice');
    Route::GET('/notice/add', 'Backend\Notice\NoticeController@add')->name('notice.add');
    Route::POST('/notice/create', 'Backend\Notice\NoticeController@create')->name('notice.create');
    Route::GET('/notice/edit/{id}', 'Backend\Notice\NoticeController@edit')->name('notice.edit');
    Route::POST('/notice/update', 'Backend\Notice\NoticeController@update')->name('notice.update');
    Route::GET('/notice/activeUser/{id}', 'Backend\Notice\NoticeController@activeUser')->name('notice.activeUser');
    Route::GET('/notice/deactiveUser/{id}', 'Backend\Notice\NoticeController@deactiveUser')->name('notice.deactiveUser');

    Route::GET('/designation/add', 'Backend\Designation\DesignationController@add')->name('designation.add');
    Route::POST('/designation/create', 'Backend\Designation\DesignationController@create')->name('designation.create');
    Route::GET('/designation', 'Backend\Designation\DesignationController@index')->name('designation');
    Route::GET('/designation/edit/{id}', 'Backend\Designation\DesignationController@edit')->name('designation.edit');
    Route::POST('/designation/update', 'Backend\Designation\DesignationController@update')->name('designation.update');
    Route::GET('/designation/delete/{id}', 'Backend\Designation\DesignationController@destroy')->name('designation.delete');
   
});

Route::group(['middleware' => ['role:administrator|admin|Level1(TSM)']], function () {
    Route::GET('/areas', 'Backend\Area\AreaController@index')->name('areas');
    Route::GET('/areas/add', 'Backend\Area\AreaController@add')->name('areas.add');
    Route::POST('/areas/create', 'Backend\Area\AreaController@create')->name('areas.create');
    Route::GET('/areas/edit/{id}', 'Backend\Area\AreaController@edit')->name('areas.edit');
    Route::POST('/areas/update', 'Backend\Area\AreaController@update')->name('areas.update');
    Route::GET('/areas/delete/{id}', 'Backend\Area\AreaController@delete')->name('areas.delete');
    Route::GET('/areas/showAllDataLocation/{id}', 'Backend\Area\AreaController@showAllDataLocation')->name('areas.showAllDataLocation');
    Route::POST('/areas/storeLocation', 'Backend\Area\AreaController@storeLocation')->name('areas.storeLocation');
    Route::POST('/areas/deleteLocationTable', 'Backend\Area\AreaController@deleteLocationTable')->name('areas.deleteLocationTable');

});


/*
|--------------------------------------------------------------------------
| administrator|admin
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin|staff']], function () {
    Route::GET('/analytics', 'Backend\Analytic\AnalyticsController@index')->name('analytics');
});

/*
|--------------------------------------------------------------------------
| administrator|admin|editor|guest
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin|staff|guest|Level1(TSM)']], function () {
    Route::GET('/checkProductVerify', 'MainController@checkProductVerify')->name('checkProductVerify');

    Route::GET('/profile/details', 'Backend\Profile\ProfileController@details')->name('profile.details');
    Route::POST('/profile/update', 'Backend\Profile\ProfileController@update')->name('profile.update');
});


/*
|--------------------------------------------------------------------------
| administrator|admin|staff
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin|staff']], function () {
    Route::GET('/attendances', 'Backend\Attendance\AttendanceController@index')->name('attendances');
    Route::post('/excel_export', 'Backend\Attendance\AttendanceController@excelExport');
    Route::GET('attendance-management', 'Backend\Attendance\AttendanceController@attendanceView')->name('attendance-management');
    Route::post('/update-attendance/{id}', 'Backend\Attendance\AttendanceController@attendanceUpdate');
    Route::GET('/horizontal-attendances', 'Backend\Attendance\AttendanceController@horizontal_attendances')->name('horizontal_attendances');
    Route::GET('attendance-regularization', 'Backend\AttendanceRegularization\AttendanceRegularizationController@attendanceRegularList')->name('attendance-regularization');
    Route::GET('/update-status/{id}/{status}', 'Backend\AttendanceRegularization\AttendanceRegularizationController@updateStatus');

});

/*
|--------------------------------------------------------------------------
| administrator Roles Module
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator']], function () {
    Route::GET('/roles', 'Backend\Roles\RoleController@index')->name('roles');
    Route::post('/update-role/{roleId}', 'Backend\Roles\RoleController@update');
});
/*
|--------------------------------------------------------------------------
| administrator|admin
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin']], function () {
    Route::GET('/messages', 'Backend\Message\MessageController@index')->name('message');
});

Route::post('reinputkey/index/{code}', 'Utils\Activity\ReinputKeyController@index');

Route::get('privacy-policy', function(){
    return view('privacyPolicy');
});

Route::get('/test-log', function () {
    \Log::error('This is an example error message sent to Slack channel.');
    return 'Log triggered.';
});
