<?php

namespace App\Http\Controllers\Api\Cmt;

use Config;
use Response;
use Carbon\Carbon;
use App\Models\Area;
use App\Models\User;
use App\Models\TsmEmp;
use App\Models\RsmTsm;
use App\Models\TsmArea;
use App\Models\Setting;
use App\Models\Location;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CmtController extends Controller
{
   
    //all branch list for cmt role
    public function branchList(){

        // $barnch = Area::orderBy('id','desc')->get();
        // $array = [];
        // $rsmName = Null;
        // foreach($barnch as $value){
        //     if(!empty($value->id)){
        //         $rsmId   = TsmArea::where('area_id',$value->id)->pluck('tsm_id');
        //     }
        //     if(!empty($rsmId)){
        //         $rsmName = User::where('role',6)->whereIn('id',$rsmId)->first();
        //     }

        //     //count absent and late mark staff 
        //     $userslist       = User::where('area_id', $value->id)->pluck('id')->toArray();
        //     $staffAttendance = Attendance::where('in_location_id', $value->id)->where('date', date('Y-m-d'))->where('status', 1)->pluck('worker_id')->toArray();
        //     //merge check in staff and absent staff
        //     $absentStaff     = array_diff($userslist,$staffAttendance);
        //     //get absent staff
        //     $count =0;
        //     $count = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,7,8,9])->count();
           
        //     $array[] = array(
        //         'id'          =>$value->id ?? Null,
        //         'sole_id'     =>$value->name ?? Null,
        //         'branch_name' =>$value->address ?? Null,
        //         'rsm_name'    =>$rsmName->name ?? Null,
        //         'total_staff_count' =>$count ?? 0
        //     );
        // }
       
        // $data['message']     = "All branch List";
        // $data['branch_list'] = $array;
        // return response()->json($data,200);
        $sql = "SELECT A.*, B.total_staff_count FROM (
        SELECT C.*, D.rsm_name FROM (
            SELECT id, name AS 'sole_id', address AS 'branch_name'
            FROM areas
            ) C
            LEFT JOIN (
                SELECT areas.id, users.name AS 'rsm_name'
                FROM areas
                LEFT JOIN tsm_areas ON tsm_areas.area_id = areas.id
                LEFT JOIN users ON users.id = tsm_areas.tsm_id
                WHERE users.role = 6
            ) D
        ON C.id = D.id
        ) A   
        LEFT JOIN (
            SELECT  X.area_id, COUNT(X.id) AS 'total_staff_count' FROM (
                SELECT users.id, users.name AS 'User Name', users.role, users.area_id, areas.name AS 'Area Name', areas.address 
                FROM `users` 
                LEFT JOIN areas on areas.id = users.area_id 
                WHERE users.role NOT IN (1,2,4,7,8,9)
                AND users.status = 1
            ) X
            LEFT JOIN (
                SELECT users.id AS 'user_id', attendances.date, attendances.status FROM attendances 
                LEFT JOIN users ON users.id = attendances.worker_id
                WHERE attendances.date = '".date('Y-m-d')."'
            ) Y 
            ON X.id = Y.user_id
            WHERE Y.status NOT IN (1,2,3,4,6,8)
            OR Y.status IS Null
            GROUP BY X.area_id
        ) B 
        ON A.id = B.area_id";

        $count = DB::select($sql);
        $data['status'] = 'success';
        $data['message']     = "All branch List";
        $data['branch_list'] = $count;
        return response()->json($data,200);
    }

    //all staff attendance based on branch id
    public function cmtAttendanceList(Request $request){
            
        //get all staff
        if(Auth::user()->role ==9){

            $tDate    = Carbon::today()->format('Y-m-d');
            $branchId = $request->branch_id;  
            //get all  branch staff
            $userslist =[];
            $allareauserslist =[];

            $userslist = User::where('area_id', $branchId)->where('status',1)->pluck('id')->toArray();
            
            // if(!empty($userslist)){
            //     $arealist       = TsmArea::whereIn('tsm_id',$userslist)->pluck('area_id')->toArray();
            //     if(!empty($arealist)){
            //         $allareauserslist      = User::whereIn('area_id', $arealist)->where('status',1)->pluck('id')->toArray();
            //     }
            // }
           
            // $allstaff = array_merge($userslist,$allareauserslist);
           
            //check allattendance list
            $staffAttendance = Attendance::whereIn('worker_id', $userslist)->where('date', $tDate)->where('status',1)->pluck('worker_id')->toArray();
            //merge check in staff and absent staff
            $absentStaff     = array_diff($userslist,$staffAttendance);
           
            //get absent staff
            $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,7,8,9])->get();
           
            $array = [];
            
            foreach($absentStaff as $key=>$value){
                $areas = Null;
                $attendances = Null;
                //get areas
                $area = Area::where('id',$value->area_id)->first();
                $areas = array(
                    "id"=> $area->id,
                    "name"=> $area->name,
                    "address"=> $area->address,
                    "state"=> "",
                    "company_id"=> $area->company_id,
                    "created_at"=> $area->created_at,
                    "updated_at"=> $area->updated_at
                ); 
                
                //get attendance
                $attendance = Attendance::where('worker_id',$value->id)->where('in_location_id',$branchId)->where('date', $tDate)->whereNotIn('worker_role_id',[1,2,4,7,8,9])->first();
                if(!empty($attendance)){
                    $attendances = array(
                        "id"=> $attendance->id,
                        "worker_id"=> $attendance->worker_id,
                        "worker_role_id"=> $attendance->worker_role_id,
                        "worker_device_id"=> $attendance->worker_device_id,
                        "date"=> date('Y-m-d',strtotime($attendance->date)),
                        "in_time"=> $attendance->in_time,
                        "out_time"=> $attendance->out_time,
                        "work_hour"=> $attendance->work_hour,
                        "over_time"=> $attendance->over_time,
                        "late_time"=>$attendance->late_time,
                        "early_out_time"=> $attendance->early_out_time,
                        "additional_status" => $attendance->additional_status ?? Null,
                        "in_location_id"=> $attendance->in_location_id,
                        "in_lat_long"=> $attendance->in_lat_long,
                        "out_location_id"=> $attendance->out_location_id,
                        "out_lat_long"=> $attendance->out_lat_long,
                        "status"=> $attendance->status,
                        "status_updated_at"=> $attendance->status_updated_at,
                        "status_updated_by"=> $attendance->status_updated_by,
                        "reason"=> $attendance->reason,
                        "image"=> $attendance->image,
                        "created_at"=>$attendance->created_at,
                        "updated_at"=>$attendance->updated_at
                    ); 
                }
                
                $array[] =array(
                    "id"=> $value->id,
                    "name"=> $value->name,
                    "email"=> $value->email,
                    "device_id"=> $value->device_id,
                    "email_verified_at"=> $value->email_verified_at,
                    "reset_token"=> $value->reset_token,
                    "reset_token_expiry"=> $value->reset_token_expiry,
                    "image"=>$value->image,
                    "role"=>$value->role,
                    "created_at"=> $value->created_at,
                    "updated_at"=> $value->updated_at,
                    "emp_id"=>$value->emp_id,
                    "area_id"=> $value->area_id,
                    "designation"=>$value->designation,
                    "mobile_number"=>$value->mobile_number,
                    "blood_group"=> $value->blood_group,
                    "emergency_contact"=>$value->emergency_contact,
                    "status"=> $value->status,
                    "deactivated_by"=> $value->deactivated_by,
                    "deactivated_at"=> $value->deactivated_at,
                    "is_login"=> $value->is_login,
                    'area' =>$areas,
                    'attendances' =>$attendances,
                );
            }

            //get tsm list based on branch
           //get tsm list based on branch
        
        if(!empty($request->rsm_id)){
            $id      = $request->rsm_id;
            $tsm_ids = RsmTsm::where('rsm_id', $id)->pluck('tsm_id')->toArray();
            $userslist =[];
            $staffAttendance =[];
            if(!empty($tsm_ids)){
                $userslist       = User::whereIn('id', $tsm_ids)->where('status',1)->pluck('id')->toArray();
                
                $staffAttendance = Attendance::whereIn('worker_id', $userslist)->where('date', date('Y-m-d'))->whereIn('status', [1,2,3,4,5,6,7,8])->pluck('worker_id')->toArray();
                //merge check in staff and absent staff
                $absentStaff     = array_merge($userslist,$staffAttendance);
                
                //get absent staff
                $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,6,7,8,9])->get();
                $tsmList=[];
                $areas = Null;
                $attendances = Null;
                foreach($absentStaff as $key=>$value){
                    //get areas
                    $area = Area::where('id',$value->area_id)->first();
                    $areas = array(
                        "id"=> $area->id ?? Null,
                        "name"=> $area->name ?? Null,
                        "address"=> $area->address ?? Null,
                        "state"=> "",
                        "company_id"=> $area->company_id ?? Null,
                        "created_at"=> $area->created_at ?? Null,
                        "updated_at"=> $area->updated_at ?? Null
                    ); 
                    
                    //get attendance
                    $attendance = Attendance::where('worker_id', $value->id)->where('date', date('Y-m-d'))->whereIn('worker_role_id',[5])->whereIn('status', [1,2,3,4,5,6,7,8])->first();
                    if(!empty($attendance)){
                        $attendances = array(
                            "id"=> $attendance->id,
                            "worker_id"=> $attendance->worker_id,
                            "worker_role_id"=> $attendance->worker_role_id,
                            "worker_device_id"=> $attendance->worker_device_id,
                            "date"=> $attendance->date,
                            "in_time"=> $attendance->in_time,
                            "out_time"=> $attendance->out_time,
                            "work_hour"=> $attendance->work_hour,
                            "over_time"=> $attendance->over_time,
                            "late_time"=>$attendance->late_time,
                            "early_out_time"=> $attendance->early_out_time,
                            "in_location_id"=> $attendance->in_location_id,
                            "in_lat_long"=> $attendance->in_lat_long,
                            "out_location_id"=> $attendance->out_location_id,
                            "out_lat_long"=> $attendance->out_lat_long,
                            "status"=> $attendance->status,
                            "additional_status" => $attendance->additional_status ?? Null,
                            "status_updated_at"=> $attendance->status_updated_at,
                            "status_updated_by"=> $attendance->status_updated_by,
                            "reason"=> $attendance->reason,
                            "image"=> $attendance->image,
                            "created_at"=>$attendance->created_at,
                            "updated_at"=>$attendance->updated_at
                        ); 
                    }
                    
                    $tsmList[] =array(
                        "id"=> $value->id,
                        "name"=> $value->name,
                        "email"=> $value->email,
                        "device_id"=> $value->device_id,
                        "email_verified_at"=> $value->email_verified_at,
                        "reset_token"=> $value->reset_token,
                        "reset_token_expiry"=> $value->reset_token_expiry,
                        "image"=>$value->image,
                        "role"=>$value->role,
                        "created_at"=> $value->created_at,
                        "updated_at"=> $value->updated_at,
                        "emp_id"=>$value->emp_id,
                        "area_id"=> $value->area_id,
                        "designation"=>$value->designation,
                        "mobile_number"=>$value->mobile_number,
                        "blood_group"=> $value->blood_group,
                        "emergency_contact"=>$value->emergency_contact,
                        "status"=> $value->status,
                        "deactivated_by"=> $value->deactivated_by,
                        "deactivated_at"=> $value->deactivated_at,
                        "is_login"=> $value->is_login,
                        'area' =>$areas,
                        'attendances' =>$attendances,
                    );
                }  
            }
            
        }
        
            $data['status'] = 'success';
            $data['message'] = 'All Staff List.';
            $data['image_url']=env('IMAGE_URL')."public/uploads/";
            $data['all_staff']  =  $array ;
            $data['tsm_list']       = $tsmList ;
            return response()->json($data, 200);
        }    
    }
    
    //get staff 20 days attendance and filter
    public function cmtStaffAttendance(Request $request){
        //get all staff
        if(Auth::user()->role ==9){
            
            $tDate      = Carbon::today()->format('Y-m-d');
            $worker_id  = $request->worker_id;  
            
            //filter parameter
            $startDate  = $request->start_date;
            $endDate    = $request->end_date;
            
           
            //get staff
            $userslist  = User::where('id', $worker_id)->where('status',1)->first();
           
            $staffAttendance = Attendance::where('worker_id', $userslist->id)->whereBetween('date', [Carbon::now()->subDays(20)->format('Y-m-d')." 00:00:00", Carbon::now()->format('Y-m-d')." 23:59:59"])->pluck('worker_id')->toArray();
            
            //merge check in staff and absent staff
            $absentStaff = array_merge((array)$userslist->id,$staffAttendance);

            //get all staff
            $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,7,8,9])->get();
            $array = Null;
            
            foreach($absentStaff as $key=>$value){
                $areas = Null;
                $attendances = [];
                //get areas
                $area = Area::where('id',$value->area_id)->first();
                $areas = array(
                    "id"=> $area->id,
                    "name"=> $area->name,
                    "address"=> $area->address,
                    "state"=> "",
                    "company_id"=> $area->company_id,
                    "created_at"=> $area->created_at,
                    "updated_at"=> $area->updated_at
                ); 
               
                //get attendance
                // $attendanceDate =[];
                
                if($startDate || $endDate){
                    $dates      = $this->getBetweenDates($startDate,$endDate);
                   
                    $i=0;
                    $dateArr = $dates;
                    $cnt = count($dates);

                    for($i=0;$i<$cnt; $i++){
                        $attendance = Attendance::where('worker_id' ,$value->id)->where('date', $dateArr[$i])->whereNotIn('worker_role_id',[1,2,4,7,8,9])->first();
                        $attendances[] = array(
                            "id"=> $attendance->id ?? Null,
                            "worker_id"=> $attendance->worker_id ?? Null,
                            "worker_role_id"=> $attendance->worker_role_id ?? Null,
                            "worker_device_id"=> $attendance->worker_device_id ?? Null,
                            "date"   => $dateArr[$i] ?? Null,
                            "in_time"=> $attendance->in_time ?? Null,
                            "out_time"=> $attendance->out_time ?? Null,
                            "work_hour"=> $attendance->work_hour ?? Null,
                            "over_time"=> $attendance->over_time ?? Null,
                            "late_time"=>$attendance->late_time ?? Null,
                            "early_out_time"=> $attendance->early_out_time ?? Null,
                            "in_location_id"=> $attendance->in_location_id ?? Null,
                            "in_lat_long"=> $attendance->in_lat_long ?? Null,
                            "out_location_id"=> $attendance->out_location_id ?? Null,
                            "out_lat_long"=> $attendance->out_lat_long ?? Null,
                            "status"=> $attendance->status ?? 5,
                            "additional_status" => $attendance->additional_status ?? Null,
                            "status_updated_at"=> $attendance->status_updated_at ?? Null,
                            "status_updated_by"=> $attendance->status_updated_by ?? Null,
                            "reason"=> $attendance->reason ?? Null,
                            "image"=> $attendance->image ?? Null,
                            "created_at"=>$attendance->created_at ?? Null,
                            "updated_at"=>$attendance->updated_at ?? Null
                        ); 
                    
                    }
                      
                }else{
                    
                    $attendance = Attendance::where('worker_id' ,$value->id)->whereBetween('date', [Carbon::now()->subDays(20)->format('Y-m-d')." 00:00:00", Carbon::now()->format('Y-m-d')." 23:59:59"])->whereNotIn('worker_role_id',[1,2,4,7,8,9])->get();
                    if(!empty($attendance)){
                        foreach($attendance as $values){
                        
                            $attendances[] = array(
                                "id"=> $values->id,
                                "worker_id"=> $values->worker_id,
                                "worker_role_id"=> $values->worker_role_id,
                                "worker_device_id"=> $values->worker_device_id,
                                "date"   => date('Y-m-d',strtotime($values->date)),
                                "in_time"=> $values->in_time,
                                "out_time"=> $values->out_time,
                                "work_hour"=> $values->work_hour,
                                "over_time"=> $values->over_time,
                                "late_time"=>$values->late_time,
                                "early_out_time"=> $values->early_out_time,
                                "in_location_id"=> $values->in_location_id,
                                "in_lat_long"=> $values->in_lat_long,
                                "out_location_id"=> $values->out_location_id,
                                "out_lat_long"=> $values->out_lat_long,
                                "status"=> $values->status,
                                "additional_status" => $values->additional_status ?? Null,
                                "status_updated_at"=> $values->status_updated_at,
                                "status_updated_by"=> $values->status_updated_by,
                                "reason"=> $values->reason,
                                "image"=> $values->image,
                                "created_at"=>$values->created_at,
                                "updated_at"=>$values->updated_at
                            ); 
                        }
                    }
                    
                }
                
                $array =array(
                    "id"=> $value->id,
                    "name"=> $value->name,
                    "email"=> $value->email,
                    "device_id"=> $value->device_id,
                    "email_verified_at"=> $value->email_verified_at,
                    "reset_token"=> $value->reset_token,
                    "reset_token_expiry"=> $value->reset_token_expiry,
                    "image"=>$value->image,
                    "role"=>$value->role,
                    "created_at"=> $value->created_at,
                    "updated_at"=> $value->updated_at,
                    "emp_id"=>$value->emp_id,
                    "area_id"=> $value->area_id,
                    "designation"=>$value->designation,
                    "mobile_number"=>$value->mobile_number,
                    "blood_group"=> $value->blood_group,
                    "emergency_contact"=>$value->emergency_contact,
                    "status"=> $value->status,
                    "deactivated_by"=> $value->deactivated_by,
                    "deactivated_at"=> $value->deactivated_at,
                    "is_login"=> $value->is_login,
                    'area' =>$areas,
                    'attendances' =>$attendances,
                );
            }
            $data['status'] = 'success';
            $data['message']    = 'All Staff List.';
            $data['image_url']  =env('IMAGE_URL')."public/uploads/";
            $data['all_staff']  =  $array ;
            return response()->json($data, 200);
        }    
    }

    //get date between two date
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

    //cmt check in staff
    public function cmtCheckInStaff(Request $request){
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|max:500',
            'date'   => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message'=>$validator->errors()], 400);
        }

        if (Auth::user()->role == 9) {
            // Since CMT cann't accept attendance after 23:59 PM
            // if (Carbon::now()->format('H:i:s') > '23:59:00') {
            //     $data['message'] = 'Attendance can not be approved since the time is past 23:59 PM.';
            //     return response()->json($data, 200);
               
            // }

            $staff = User::where('id',$request->staff_id)->first();
            if(empty($staff)){
                $data['status'] = 'error';
                $data['message'] = 'Staff id invalid!';
                return response()->json($data, 400);
            }

            $staffs = User::where('id',$request->cmt_id)->where('role',9)->first();
            if(empty($staffs)){
                $data['status'] = 'error';
                $data['message'] = 'Cmt id invalid!';
                return response()->json($data, 400);
            }

            // $area = TsmArea::where('tsm_id',$request->rsm_id)->first();
            $staffArea = User::where('id',$request->staff_id)->first();
        
            if(!empty($staffArea->area_id)){
                $location = DB::table('location_coordinates')->where('area_id', $staffArea->area_id)->get(['lat', 'long']);
            }

            //check attendance status 5 when update status
            // $obstOdCount = Attendance::where('worker_id',$request->staff_id)->where('worker_role_id',3)->whereMonth('date',date('m'))->where('status',3)->count();
            // if($obstOdCount >= 5){
            //     return response()->json(['status' => 'error', 'message' => 'Attendance can not be marked', 'data' => []], 200);
            // }else{

                $checkAttdance = Attendance::where('worker_id',$request->staff_id)->where('date',$request->date)->whereIn('status',[1,2,3,4,5,6,7,8])->first();
                if(!empty($checkAttdance)){
                    $checkAttdance->worker_id = $request->staff_id ?? Null;
                    $checkAttdance->reason = $request->reason ?? Null;
                    $checkAttdance->date   = $request->date ?? Null;
                    $checkAttdance->in_time = $checkAttdance->in_time ?? date('H:i:s');
                    $checkAttdance->status = $request->status ?? Null;
                    $checkAttdance->additional_status = $request->additional_status ?? Null;
                    $checkAttdance->status_updated_at = Carbon::now();
                    $checkAttdance->status_updated_by = $request->cmt_id ?? Null;
                    $checkAttdance->save();
                    $data['status'] = 'success';
                    $data['message'] = 'Attendance marked successfully.';
                    return response()->json($data, 200);
                }else{
                    $attendance = new Attendance();
                    $attendance->worker_id = $request->staff_id;
                    $attendance->reason = $request->reason;
                    $attendance->worker_device_id = $request->device_id;
                    $attendance->worker_role_id = $staffArea->role;
                    $attendance->date   = $request->date;
                    $attendance->status = $request->status;
                    $attendance->status_updated_at = Carbon::now();
                    $attendance->status_updated_by = $request->cmt_id;
                    $attendance->additional_status = $request->additional_status ?? Null;
                    $attendance->in_time         = date('H:i:s');
                    $attendance->in_location_id	 = $staffArea->area_id;
                    if(!empty($location[0]->lat) || !empty($location[0]->long)){
                        $attendance->in_lat_long     = $location[0]->lat.",".$location[0]->long ?? Null;
}
                    $attendance->created_at = Carbon::now();
                    $attendance->updated_at = Carbon::now();
                    $attendance->save();
                    $data['status'] = 'success';
                    $data['message'] = 'Attendance marked successfully.';
                    return response()->json($data, 200);
                    
                } 
        //    }
        }
    }

    public function filterStaffAttendance(Request $request){

       $branchId   = $request->branch_id;
       $soleId     = $request->sole_id;
       $branchName = $request->branch_name;
       
       //search parameter 
       $branch = Null;
       if($branchId){
          $branch = Area::where('id','LIKE',"%{$branchId}%")->first();
       }
       if($soleId){
          $branch = Area::where('name','LIKE',"%{$soleId}%")->first();
       }
       if($branchName){
          $branch = Area::where('address','LIKE',"%{$branchName}%")->first();
       }

        $tDate    = Carbon::today()->format('Y-m-d');
       
        //get all  branch staff
        $userslist =[];
        $allareauserslist =[];

        $userslist = User::where('area_id', $branch->id)->where('status',1)->pluck('id')->toArray();
        
        if(!empty($userslist)){
            $arealist       = TsmArea::whereIn('tsm_id',$userslist)->pluck('area_id')->toArray();
            if(!empty($arealist)){
                $allareauserslist      = User::whereIn('area_id', $arealist)->where('status',1)->pluck('id')->toArray();
            }
        }
        
        $allstaff = array_merge($userslist,$allareauserslist);
        
        //check allattendance list
        $staffAttendance = Attendance::whereIn('worker_id', $allstaff)->where('date', $tDate)->pluck('worker_id')->toArray();
        //merge check in staff and absent staff
        $absentStaff     = array_unique(array_merge($allstaff,$staffAttendance));
        
        //get absent staff
        if($branch){
            $absentStaff = User::where('area_id',$branch->id)->where('status',1)->whereIn('id', $absentStaff)->get();
        }else{
            $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->get();
        }
        
        
        $array = [];
        
        foreach($absentStaff as $key=>$value){
            $areas = Null;
            $attendances = Null;
            //get areas
            $area = Area::where('id',$value->area_id)->first();
            $areas = array(
                "id"=> $area->id,
                "name"=> $area->name,
                "address"=> $area->address,
                "state"=> "",
                "company_id"=> $area->company_id,
                "created_at"=> $area->created_at,
                "updated_at"=> $area->updated_at
            ); 
            
            //get attendance
            $attendance = Attendance::where('worker_id',$value->id)->where('in_location_id',$branchId)->where('date', $tDate)->first();
            if(!empty($attendance)){
                $attendances = array(
                    "id"=> $attendance->id,
                    "worker_id"=> $attendance->worker_id,
                    "worker_role_id"=> $attendance->worker_role_id,
                    "worker_device_id"=> $attendance->worker_device_id,
                    "date"=> date('Y-m-d',strtotime($attendance->date)),
                    "in_time"=> $attendance->in_time,
                    "out_time"=> $attendance->out_time,
                    "work_hour"=> $attendance->work_hour,
                    "over_time"=> $attendance->over_time,
                    "late_time"=>$attendance->late_time,
                    "early_out_time"=> $attendance->early_out_time,
                    "in_location_id"=> $attendance->in_location_id,
                    "in_lat_long"=> $attendance->in_lat_long,
                    "out_location_id"=> $attendance->out_location_id,
                    "out_lat_long"=> $attendance->out_lat_long,
                    "status"=> $attendance->status,
                    "status_updated_at"=> $attendance->status_updated_at,
                    "status_updated_by"=> $attendance->status_updated_by,
                    "reason"=> $attendance->reason,
                    "image"=> $attendance->image,
                    "created_at"=>$attendance->created_at,
                    "updated_at"=>$attendance->updated_at
                ); 
            }
            
            $array[] =array(
                "id"=> $value->id,
                "name"=> $value->name,
                "email"=> $value->email,
                "device_id"=> $value->device_id,
                "email_verified_at"=> $value->email_verified_at,
                "reset_token"=> $value->reset_token,
                "reset_token_expiry"=> $value->reset_token_expiry,
                "image"=>$value->image,
                "role"=>$value->role,
                "created_at"=> $value->created_at,
                "updated_at"=> $value->updated_at,
                "emp_id"=>$value->emp_id,
                "area_id"=> $value->area_id,
                "designation"=>$value->designation,
                "mobile_number"=>$value->mobile_number,
                "blood_group"=> $value->blood_group,
                "emergency_contact"=>$value->emergency_contact,
                "status"=> $value->status,
                "deactivated_by"=> $value->deactivated_by,
                "deactivated_at"=> $value->deactivated_at,
                "is_login"=> $value->is_login,
                'area' =>$areas,
                'attendances' =>$attendances,
            );
        }
        $data['status'] = 'success';
        $data['message'] = 'All Staff List.';
        $data['image_url']=env('IMAGE_URL')."public/uploads/";
        $data['all_staff']  =  $array ;
        return response()->json($data, 200);


    }
   
}    