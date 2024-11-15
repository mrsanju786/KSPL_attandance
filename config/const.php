<?php

return [

    /*
    |--------------------------------------------------------------------------
    | write constants for this project
    |--------------------------------------------------------------------------
    */

    'SUCCESS_CREATE_MESSAGE' => 'The entered content was saved.',        // success when data created
    'FAILED_CREATE_MESSAGE'  => 'We could not save your entries.', // failed when data created
    'SUCCESS_UPDATE_MESSAGE' => 'The edited content was saved.',        // success when data updated
    'FAILED_UPDATE_MESSAGE'  => 'The edited content could not be saved.', // failed when data updated
    'SUCCESS_DELETE_MESSAGE' => 'The target data was deleted.',        // success when data deleted
    'FAILED_DELETE_MESSAGE'  => 'The target data could not be deleted.', // failed when data deleted
    'FAILED_DELETE_SELF_MESSAGE'  => 'The target data could not be deleted, can\'t delete your account.', // failed when data deleted self account
    'FAILED_VALIDATOR'       => 'Please check the form below for errors.', // failed when validator not pass
    'ERROR_FOREIGN_KEY' => 'Cannot delete this data, because this data is used in other data.',

    'UPLOAD_PATH' => public_path('uploads/'),

    'SUCCESS_USER_ACTIVE_MESSAGE' => 'The User is activated.',        // success user active
    'SUCCESS_USER_DEACTIVE_MESSAGE' => 'The User is deactivated.',        // success user deactive
    'SUCCESS_ATTENDANCE_MESSAGE' => 'Attendance marked successfully.',  
    'ERROR_ATTENDANCE_MESSAGE' => 'Attendance marked already.',  
    'SUCCESS_HOLIDAY_ADDED_MESSAGE' => 'Holiday added successfully!.',  
    'SUCCESS_UPDATE_HOLIDAY_MESSAGE' => 'Holiday updated successfully!.',         // success when data updated

    'SUCCESS_NOTICE_MESSAGE'        => 'Notice added successfully!.',   
    'SUCCESS_UPDATE_NOTICE_MESSAGE' => 'Notice updated successfully!.',  
    'SUCCESS_DELETE_NOTICE_MESSAGE' => 'Notice deleted successfully!.', 
    'SUCCESS_NOTICE_ACTIVE_MESSAGE' => 'Notice activated successfully!.',
    'SUCCESS_NOTICE_DEACTIVE_MESSAGE' => 'Notice deactivated successfully!.',
    'SUCCESS_LEAVEBALANCE_ADDED_MESSAGE' => 'Leave Balance added successfully!',
    'FAILED_CREATE_LEAVEBALANCE_MESSAGE' => 'Leave Balance not added!',
    'SUCCESS_UPDATE_LEAVEBALANCE_MESSAGE' =>'Leave Balance updated successfully!',
    'SUCCESS_DELETE_LEAVEBALANCE_MESSAGE' => 'Leave Balance deleted successfully!',
    'FAILED_DELETE_LEAVEBALANCE_MESSAGE' => 'Leave Balance failed!' 
];
