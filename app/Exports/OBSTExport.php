<?php

namespace App\Exports;
use App\Models\Area;
use App\Models\User;
use App\Models\Role;
use App\Models\RsmTsm;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;
use Session;
use Log;
use Carbon\Carbon;

class OBSTExport implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function view(): View
    {
        $date1 = Session::get('date1');
        $date2 = Session::get('date2');
        $absentUser =[];
        // $all_users = User::where('role', 3)
        //                 ->get('id');
                       
        $activeUsers = User::where('role', 3)
        ->where('status',1)
        ->get();

        $deactiveusers = User::where('role', 3)
                ->where('status',0)
                ->whereBetween('deactivated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->get();
                
        $absentUser = $activeUsers->merge($deactiveusers)->unique('id');

        foreach($absentUser as $usersDetails){
            $addr = Area::where('id', $usersDetails->area_id)->first('address');
            $usersDetails->worker_role_id = 3;
            $usersDetails->address = $addr->address ?? "-";
            $usersDetails->worker_device_id = null;
            $usersDetails->in_time = null;
            $usersDetails->date = null;
            $usersDetails->user_name = $usersDetails->name;
            $usersDetails->user_status = $usersDetails->status;
            $usersDetails->worker_id = $usersDetails->id;
            $usersDetails->attnId = 2;
        }
        
        $dates = getBetweenDates($date1, $date2);
        $cnt = count($dates);

        foreach($absentUser as $key=>$values){
            $rsm_name = Null;
            $rsm_name1 =Null; 
            $rsm_name2 =Null; 
            $addr = Area::where('id', $values->area_id)->first('address');
            $emp  = DB::table('rsm_tsms')->where('tsm_id',$values->id)->pluck('rsm_id')->toArray();
            $tsm  = DB::table('tsm_emps')->where('emp_id',$values->id)->pluck('tsm_id')->toArray();
    
            $values->address = $addr->address ?? "-";
            $values->worker_role_id = $values->role;
            $values->user_name = $values->name;
            $values->user_status = $values->status;
            $values->worker_id = $values->id;
            $values->attnId = 2;


            if(!empty($emp)){
                $rsm_name = DB::table('users')->whereIn('id',$emp)->where('role',6)->where('status',1)->first();
               
            }
    
            if(!empty($tsm)){
                $rsm_name1 = DB::table('users')->whereIn('id',$tsm)->where('role',6)->where('status',1)->first();
    
                $tsmEmp = RsmTsm::whereIn('tsm_id',$tsm)->pluck('rsm_id')->toArray();
                if(!empty($tsmEmp)){
                    $rsm_name1 = DB::table('users')->whereIn('id',$tsmEmp)->where('role',6)->where('status',1)->first();
                   
                }
            
            }

           
            
            $attdence = DB::table('attendances')->where('worker_id',$values->id)->orderBy('id','desc')->first();
            $soleId = DB::table('areas')->where('id',$values->area_id)->first();

            if(in_array($values->role, [3,5,6])) {
                $values->emp_id;
                $values->user_name;


                if($values->role ==3) {
                    $values->rsm_name = !empty($rsm_name1->name) ? $rsm_name1->name: "-";
                }
                if($values->role ==5) {
                    $values->rsm_name = !empty($rsm_name->name) ? $rsm_name->name: "-";
                }
                if($values->role ==6) {
                 $values->rsm_name = "-";
                 }


                if($values->user_status == 1) {
                    $values->user_status = "Active";
                }
                else {
                    $values->user_status = "Deactive";
                }

                $values->soleId =  !empty($soleId->name) ? $soleId->name  : "-";
                $values->address =  !empty($values->address) ? $values->address  : "-";
                $values->worker_device_id = !empty($attdence->worker_device_id) ? $attdence->worker_device_id  : "-"; 
                $values->in_time = !empty($attdence->in_time) ? $attdence->in_time  : "-";
                $values->out_time = !empty($attdence->out_time) ? $attdence->out_time  : "-";                
            }

            $p_count = 0;
            $A_count = 0;
            $pa=0;
            $od=0;
            $l=0;
            $a=0;
            $wo=0;
            $pl=0;
            $h=0;

            $dat_att_arr = [];
            
            // Fetch attendance records and leave logs for the given dates
            $attendances = DB::table('attendances')
                ->where('worker_id', $values->worker_id)
                ->where('attendances.worker_role_id', $values->worker_role_id)
                ->whereIn('date', $dates)
                ->get();
            
            $leaveLogs = DB::table('leave_logs')
                ->where('user_id', $values->worker_id)
                ->where('status', 1)
                ->whereIn('from_date', $dates)
                ->get();
            
            // Create an associative array to map dates to attendance data
            $attendanceData = [];
            foreach ($attendances as $attendance) {
                $attendanceData[$attendance->date] = $attendance;
            }
            
            // Create an associative array to map dates to leave log data
            $leaveLogData = [];
            foreach ($leaveLogs as $leaveLog) {
                $leaveLogData[$leaveLog->from_date] = $leaveLog;
            }
            
            foreach ($dates as $date) {
                $date_attendance = null;
            
                // Get data for the current date from the associative arrays
                $attendance = $attendanceData[$date] ?? null;
                $leaveLog = $leaveLogData[$date] ?? null;
            
                $userarea = DB::table('users')
                    ->where('id', $values->worker_id)
                    ->where('status', 1)
                    ->first();
            
                $areastate = null;
                if (!empty($userarea)) {
                    $areastate = DB::table('areas')->where('id', $userarea->area_id)->first();
                }
            
                $holiday = null;
                if (!empty($areastate)) {
                    $holiday = DB::table('holidays')
                        ->where('state_id', $areastate->state)
                        ->where('date', $date)
                        ->first();
                }
            
                $weekDay = date('w', strtotime($date));
                if ($attendance == null) {
                    if($weekDay == 0 || $weekDay == 6){
                        $wo = $wo+1;
                        $date_attendance = "WO";
                    } elseif ($leaveLog != null) {
                        $pl = $pl+1;
                        $date_attendance = 'PL';
                    } elseif ($holiday != null) {
                        $h = $h+1;
                        $date_attendance = "H";
                    } else {
                        $a = $a+1;
                        $date_attendance = "A";
                    }
                } else {
                    if (!empty($attendance->status)) {
                        if ($attendance->status == '1') {
                            $pa = $pa+1;
                            $date_attendance = "P";
                        } elseif ($attendance->status == '2') {
                            $a = $a+1;
                            $date_attendance = "A";
                        } elseif ($attendance->status == '3') {
                            $od = $od+1;
                            $date_attendance = "OD";
                        } elseif ($attendance->status == '4') {
                            $h = $h+1;
                            $date_attendance = "H";
                        } elseif ($attendance->status == '5') {
                            $a = $a+1;
                            $date_attendance = "A";
                        } elseif ($attendance->status == '6') {
                            $wo = $wo+1;
                            $date_attendance = "WO";
                        } elseif ($attendance->status == '7') {
                            $l = $l+1;
                            $date_attendance = "LT";
                        } elseif ($attendance->status == '8') {
                            $pl = $pl+1;
                            $date_attendance = "PL";
                        }

                        if($attendance->status=='1' || $attendance->status=='7' || $attendance->status=='3'){
                            $p_count++;
                        }

                    }
                }
            
                $dat_att_arr[$date] = $date_attendance;
                $values->in_time = !empty($date->in_time) ? $date->in_time  : "-";
                $values->out_time = !empty($date->out_time) ? $date->out_time  : "-";

            }

            $values->date_att = $dat_att_arr;
            $values->payable_days = $p_count + $wo + $h; 
            $values->present = !empty($pa) ? $pa: 0; 
            $values->late = !empty($l) ? $l: 0;
            $values->out_door = !empty($od) ? $od: 0;
            $values->week_off = !empty($wo) ? $wo: 0;
            $values->leave = !empty($pl) ? $pl: 0;
            $values->holiday = !empty($h) ? $h: 0; 
            $values->absent = !empty($a) ? $a: 0;

        }
       
        return view('backend.exports.attdence', ['absent_users'=>$absentUser, 'dates'=>$dates
          ]);

    }
}
function getBetweenDates($startDate, $endDate) {
    $rangArray = [];
 
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);
 
    for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
        $date = date('Y-m-d', $currentDate);
        $rangArray[] = $date;
    }
 
    return $rangArray;
}

function array_add_multiple($key_value)
    {
        $arr = [];
        foreach ($key_value as $key => $value) {
            $arr[] = [$key=>$value];
        }
        return $arr;
    }

