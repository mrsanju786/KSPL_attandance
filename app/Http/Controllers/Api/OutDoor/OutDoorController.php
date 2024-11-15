<?php

namespace App\Http\Controllers\Api\OutDoor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Config;
use Response;
use Carbon\Carbon;
use App\Models\Area;
use App\Models\Role;
use App\Models\User;
use App\Models\TsmEmp;
use App\Models\RsmTsm;
use App\Models\TsmArea;
use App\Models\Setting;
use App\Models\LeaveLog;
use App\Models\Location;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Image;
use App\Models\OutDoor;
use App\Models\Leave;

class OutDoorController extends Controller
{
    //apply od for staff
    public function applyOutDoor(Request $request){
        
        $validator = Validator::make($request->all(), [
            'date'    => 'required|string',
            'user_id' =>'required',
            'remark'  =>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' =>$validator->errors()], 400);
        }
       

        //check already attendacne for employee
        $alreadyMarkedAttendance = Attendance::where('worker_id', $request->user_id)
                                                ->where('date', $request->date)
                                                ->first();

        if(!empty($alreadyMarkedAttendance)){

            return response()->json(['status' => 'success', 'message'=>'Attendance marked already, you cannot apply OD!'], 200);
        }          
        
        
        //check already applied od
        $onlyDate = date('Y-m-d',strtotime($request->date));
        $alreadyOdApply = OutDoor::where('user_id',$request->user_id)->where('from_date',$onlyDate)->first();
        if(!empty($alreadyOdApply)){
            return response()->json(['status' => 'success', 'message'=>'OD applied already!'], 200);
        }

        $user_id    = $request->user_id;
        
        try {
           
            //check obst 5 od count condition
            // $attendance  = 0;
            // $checkMonth =  date('m',strtotime($request->date));
            // if($checkMonth <= date('m')){
                
                //     $obstUser    = User::where('id',$request->user_id)->first();
                //     if($obstUser->role ==3){
                    //         $attendance  = OutDoor::where('user_id',$obstUser->id)->whereMonth('from_date',date('m'))->count();
                    //         if($attendance >= 5 ){
                        //             return response()->json(['status' => 'success', 'message' => 'You cannot take an OD because you have already used your 5 OD for this month.', 'data' => []], 200);
                    //         }
            //     }
                
            // }

            $date = $request->date;

            $date = explode('-', $date);
            $from = Carbon::create(str_replace('/', '-', $date[0]))->format('Y-m-d');
            $to   = Carbon::create(str_replace('/', '-', $date[0]))->format('Y-m-d');
            
            DB::beginTransaction();

            $outDoor = new OutDoor();
            $outDoor->user_id    = $request->user_id;
$outDoor->od_type    = $request->od_type;
            $outDoor->from_date  = $from;
            $outDoor->to_date    = $to;
            $outDoor->remark     = $request->remark;
            $outDoor->created_by = $request->user_id;
            $outDoor->save();

            DB::commit();
            
            return response()->json(['status' => 'success', 'message'=>'OD applied successfully!'], 200);
            
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }
    }

    //out door staff list obst ,tsm ,rsm based on user id
    public function outDoorList(Request $request){

        $users   = OutDoor::where('user_id',$request->user_id)->orderBy('id','desc')->get();
        $odCount = 0;
        $odCount = OutDoor::where('user_id',$request->user_id)->where('status',0)->count();

        $array = [];
        foreach($users as $key=>$value){
            $user = User::where('status',1)->where('id',$value->user_id)->first();
            $role = Role::where('id',$user->role)->first();
            $rsm  = User::where('status',1)->where('id',$value->approved_by)->first();
            $array[] = array(
                'id' => $value->id ?? Null,
                'user_id' => $user->id ?? Null,
                'emp_name' =>$user->name ?? Null,
                'emp_id'   =>$user->emp_id ?? Null,
                'role'     =>$role->display_name ?? Null,
                'od_status' => 3 ?? Null,
'od_type'  => $value->od_type ?? Null,
                'from_date'  => $value->from_date ?? Null,
                'to_date'    => $value->to_date ?? Null,
                'remark'     => $value->remark ?? Null,
                'head_remark'     => $value->head_remark ?? Null,
                'status'          => $value->status ?? Null,
                'approved_by'     => $rsm->name ?? Null,
                'approved_at'     => $value->approved_at ?? Null,
                'applied_at'      => $value->created_at->format('Y-m-d H:i:s') ?? Null,
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Staff OD List!', 'data' => ['staff_od_list'=>$array,'pending_od'=>$odCount]], 200);
    }

     //approve od for rsm and cmt
    public function approveOD(Request $request){

        if(Auth::user()->role == 6 || Auth::user()->role == 9){

                     
            $odApprove = OutDoor::where('id',$request->od_id)->where('status',0)->first();

            //check already attendacne for employee
            $alreadyMarkedAttendance = Attendance::where('worker_id', $odApprove->user_id)
                                                ->where('date', $odApprove->from_date)
                                                ->first();

            if(!empty($alreadyMarkedAttendance)){

                return response()->json(['status' => 'success', 'message'=>'Attendance marked already, you cannot apply OD!'], 200);
            }   

            $odApprove->status      = $request->status;
            // $odApprove->head_remark = $request->head_remark;
            $odApprove->approved_by = Auth::id();
            $odApprove->approved_at = date('Y-m-d');
            $odApprove->save();
            
            if($request->status ==1){

                
                    $user = User::where('id',$odApprove->user_id)->where('status',1)->first();
                    if(!empty($user->area_id)){
                        $location = DB::table('location_coordinates')
                                       ->where('area_id', $user->area_id)
                                       ->get(['lat', 'long']);
                    }

                    $attendance = new Attendance();
                    $attendance->worker_id        = $odApprove->user_id;
                    $attendance->reason           = $odApprove->remark;
                    $attendance->worker_device_id = $user->device_id;
                    $attendance->worker_role_id   = $user->role;
                    $attendance->date             = $odApprove->from_date;
                    $attendance->status           = 3;
                                        $attendance->additional_status = Null;
                    $attendance->status_updated_at = Carbon::now();
                    $attendance->status_updated_by = Auth::user()->id;
                    $attendance->in_time           = date('H:i:s');
                    $attendance->in_location_id	   = $user->area_id;
                    $attendance->in_lat_long       = $location[0]->lat.",".$location[0]->long ?? Null;
                    $attendance->created_at        = Carbon::now();
                    $attendance->updated_at        = Carbon::now();
                    $attendance->save();
                
                return response()->json(['status' => 'success', 'message' => 'OD approved successfully!'], 200);
            }else{
                return response()->json(['status' => 'success', 'message' => 'OD rejected successfully!'], 200);
            }
            
        }
    }


    //od staff list
    public function odStaffList(Request $request){
       
        if(Auth::user()->role == 6){
           
            $userslist   = User::where('area_id', $request->branch_id)->where('status',1)->whereNotIn('role',[1,2,4,6,7,8,9])->pluck('id')->toArray();
            //get all od staff
            $odStaffList = OutDoor::whereIn('user_id',$userslist)->orderBy('id','desc')->get();
$odCount     = 0; 
            $odCount     = OutDoor::whereIn('user_id',$userslist)->where('status',0)->count();
            $array = [];
            $user  = Null;
            $role  = Null;
            foreach($odStaffList as $key=>$value){
                $user = User::where('status',1)->where('id',$value->user_id)->whereNotIn('role',[1,2,4,6,7,8,9])->first();
               
                if(!empty($user)){
                    $role = Role::where('id',$user->role)->first();
                }
                
                $array[] = array(
                    'id' => $value->id ?? Null,
                    'user_id' => $user->id ?? Null,
                    'emp_name' =>$user->name ?? Null,
                    'emp_id'   =>$user->emp_id ?? Null,
                    'role'     =>$role->display_name ?? Null,
                    'od_type' => $value->od_type ?? Null,
                    'from_date'  => $value->from_date ?? Null,
                    'to_date'    => $value->to_date ?? Null,
                    'remark'     => $value->remark ?? Null,
                    'head_remark'     => $value->head_remark ?? Null,
                    'status'     => $value->status ?? Null,
                    'approved_by'     => $value->approved_by ?? Null,
                    'approved_at'     => $value->approved_at ?? Null,
                    'applied_at'      => $value->created_at ?? Null,
                );
            }

            return response()->json(['status' => 'success', 'message' => 'Tsm and obst od list.', 'data' => ['tsm_obst_od_list' => $array,'pending_od'=>$odCount]], 200);
        }
        elseif(Auth::user()->role == 9){
            $userslist =[];
            
            $rsm        = User::where('id',$request->rsm_id)->where('status',1)->first();
            $userslist  = User::where('area_id', $rsm->area_id)->where('status',1)->whereNotIn('role',[1,2,4,7,8,9])->pluck('id')->toArray();
// $userslist   = User::where('area_id', $request->branch_id)->where('status',1)->whereNotIn('role',[1,2,4,7,8,9])->pluck('id')->toArray();
           
            $odStaffList = OutDoor::whereIn('user_id',$userslist)->orderBy('id','desc')->get();
           $odCount     = 0;
            $odCount     = OutDoor::whereIn('user_id',$userslist)->where('status',0)->count();
                      $array2 = [];
            $user  = Null;
            $role  = Null;
            foreach($odStaffList as $key=>$value){
                $user = User::where('status',1)->where('id',$value->user_id)->whereNotIn('role',[1,2,4,7,8,9])->first();
                $role = Role::where('id',$user->role)->first();
                $array2[] = array(
                    'id' => $value->id ?? Null,
                    'user_id' => $user->id ?? Null,
                    'emp_name' =>$user->name ?? Null,
                    'emp_id'   =>$user->emp_id ?? Null,
                    'role'     =>$role->display_name ?? Null,
                    'od_type' => $value->od_type ?? Null,
                    'from_date'  => $value->from_date ?? Null,
                    'to_date'    => $value->to_date ?? Null,
                    'remark'     => $value->remark ?? Null,
                    'head_remark'     => $value->head_remark ?? Null,
                    'status'     => $value->status ?? Null,
                    'approved_by'     => $value->approved_by ?? Null,
                    'approved_at'     => $value->approved_at ?? Null,
                    'applied_at'      => $value->created_at ?? Null,
                );
            }

            return response()->json(['status' => 'success', 'message' => 'Rsm od list.', 'data' => ['rsm_od_list' => $array2,'pending_od'=>$odCount]], 200);
        }
    }

    //rsm list
    public function odRsmList(){
        try{
             $rsm_list   =[];
             $pendingOd =0;
             $pendingOd = OutDoor::where('status',0)->count();
             $leaveCount = 0;
             $leaveCount = Leave::where('status',0)->count();

             $rsmList   = User::where('role',6)->where('status',1)->get();
             foreach($rsmList as $value){
                $odPending =0;
                $leavePending =0;
                $userslist = User::where('area_id',$value->area_id)->whereIn('role',[3,5,6])->pluck('id')->toArray();
                $odPending = OutDoor::whereIn('user_id',$userslist)->where('status',0)->count();
                $leavePending = Leave::whereIn('user_id',$userslist)->where('status',0)->count();
                $area      = Area::where('id',$value->area_id)->first();
                $role      = Role::where('id',$value->role)->first();
                $rsm_list[] =array(
                    'rsm_id'     =>$value->id ?? Null,
                    'emp_id'     =>$value->emp_id ?? Null,
                    'rsm_name'   =>$value->name ?? Null,
                    'role'       =>$role->display_name ?? Null,
                    'branch_id'  =>$area->id ?? Null ,
                    'sole_id'    =>$area->name ?? Null,
                    'branch_name'=>$area->address ?? Null,
                    'pending_od_count'=>$odPending  ?? 0,
                    'pending_leave_count'=>$leavePending  ?? 0
                );
             }
             
                        $data['rsm_list']   = $rsm_list;
            $data['total_pending_od'] = $pendingOd;
            $data['total_pending_leave'] = $leaveCount;
            return response()->json($data, 200);
        } catch (Exception $e) {
              return response()->json(['status' => 'error','error'=>'something went wrong!'], 400);
  
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
    
}
