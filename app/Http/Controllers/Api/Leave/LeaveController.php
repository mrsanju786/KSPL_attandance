<?php

namespace App\Http\Controllers\Api\Leave;
use Auth;
use DateTime;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Models\Leave;
use App\Models\TsmEmp;
use App\Models\User;
use App\Models\Role;
use App\Models\LeaveLog;
use App\Models\RsmTsm;
use App\Models\TsmArea;
use App\Models\LeaveBalance;
use App\Models\Designation;
use App\Mail\ApplyLeaveMail;
use App\Mail\ApproveLeaveMail;

class LeaveController extends Controller
{

    //save staff list
    public function saveLeave(Request $request){
        
        $validator = Validator::make($request->all(), [
            'date' => 'required|string',
            'leave_type' => 'required',
            'leave_duration' => 'required',
            'user_id' =>'required',
            'remark' =>'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' =>$validator->errors()], 400);
        }

        $date = $request->date;

        $date = explode('-', $date);
        $from = Carbon::create(str_replace('/', '-', $date[0]))->format('Y-m-d');
        $to   = Carbon::create(str_replace('/', '-', $date[1]))->format('Y-m-d');
        Log::debug($from);
        //check already leave applied condition
        // $from_date = date('Y-m-d',strtotime($date[0]));
        // $to_date   = date('Y-m-d',strtotime($date[1]));
        // $appliedLeave = Leave::where('user_id',$request->user_id)->where('from_date',$from_date)->orWhere('to_date',$to_date)->first();
        // if(!empty($appliedLeave)){
        //     return response()->json(['status' => 'success', 'message'=>'Leave applied already!'], 200);
        // }
       
        try {
            
            
            DB::beginTransaction();
            //check boa and dst leave approved
            $user = User::where('id',$request->user_id)->whereIn('role',[7,8])->first();
            $status =0;
            if($user){
                $status  =1;
            }else{
                $status  =0;
            }

            $leave = new Leave();
            $leave->user_id    = $request->user_id;
            $leave->leave_type = $request->leave_type;
$leave->leave_duration = $request->leave_duration;
            $leave->from_date  = $from;
            $leave->to_date    = $to;
            $leave->remark     = $request->remark;
            $leave->created_by = $request->user_id;
            $leave->status     = $status ?? 0;
            $leave->save();
            
            //leave log for boa and dst
             //store approve leave Log
             $from  = Carbon::create(str_replace('/', '-', $leave->from_date))->format('Y-m-d');
             $to    = Carbon::create(str_replace('/', '-', $leave->to_date))->format('Y-m-d');
             $dates = $this->getBetweenDates($from, $to);
             if(!empty($user)){
                foreach($dates as $key =>$value){
                    $leaveLog = new LeaveLog;
                    $leaveLog->leave_id    =$leave->id ?? Null;
                    $leaveLog->user_id     =$leave->user_id ?? Null;
                    $leaveLog->from_date   =$value ?? Null;
                    $leaveLog->leave_type  =$leave->leave_type ?? Null;
                    $leaveLog->leave_duration  =$leave->leave_duration ?? Null;
                    $leaveLog->remark      =$leave->remark ?? Null;
                    $leaveLog->head_remark =$leave->head_remark ?? Null;
                    $leaveLog->status      =$leave->status ?? Null;
                    $leaveLog->created_by  =$leave->created_by ?? Null;
                    $leaveLog->created_by  =$leave->created_by ?? Null;
                    $leaveLog->approved_by =Auth::id() ?? Null;
                    $leaveLog->approved_at =date('Y-m-d') ?? Null;
                    $leaveLog->save();
                }
             }
             
             

             //send leave apply mail
            $staff = User::where('id',$request->user_id)->where('status',1)->first();
             
            $leaveData =[
                'emp_id'=> $staff->emp_id,
                'username'=>$staff->name,
                'leave_type' => $request->leave_type,
                'leave_duration' => $request->leave_duration,
                'leave_from'  => $from,
                'leave_to'   => $to,
                'reason' => $request->remark,
            ];
            // $to_mail = $staff->email ?? Null;
            // $cc  = [];
            // if (env('LIVE_LEAVE_MAIL')) {
            //     $cc = env('LIVE_LEAVE_MAIL');
            // }
            $mail = Mail::to(['madhu@ksoftpl.com', 'hr@ksoftpl.com']);

            if ($staff->emp_id=='KSPL/HR/1048') {
                $mail->cc(['pm@ksoftpl.com']);
            }elseif($staff->emp_id=='KSPL/HR/1070'){
                $mail->cc(['pm@ksoftpl.com']);
            }else{
                $mail->cc(['pm@ksoftpl.com','jitesh.jain@kanishkasoftware.com']);
            }
             
            $mail->send(new ApplyLeaveMail($leaveData));

            DB::commit();
           // $data['message'] = 'leave applied successfully';
            return response()->json(['status' => 'success', 'message'=>'Leave applied successfully!'], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }
    }

    //leave staff list
    public function leaveStaffList(Request $request){
       
        if(Auth::user()->role == 6){
           //check rsm for area
            // $userRsm = User::where('id',$request->rsm_id)->where('area_id',$request->branch_id)->first();
            //get rsm all area
            // $rsmArea = TsmArea::where('tsm_id',$userRsm->id)->pluck('area_id');
            // //get all staff on rsm branch
            // $sameBranchUsers = User::where('area_id',$request->branch_id)->where('status',1)->whereNotIn('role',[1,2,4,6,7,8,9])->pluck('id')->toArray();
            
            //where('area_id', $request->branch_id)->

            $userslist = User::where('status',1)->whereNotIn('role',[1,2,4,6,7,8,9])->pluck('id')->toArray();
            
            // $allstaff  = array_merge($sameBranchUsers,$userslist);
            //get all leave
            $leaveStaffList = Leave::whereIn('user_id',$userslist)->orderBy('id','desc')->get();
            $leaveCount = 0;
            $leaveCount = Leave::whereIn('user_id',$userslist)->where('status',0)->count();

            $array = [];
            $user  = Null;
            $role  = Null;
            foreach($leaveStaffList as $key=>$value){
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
                    'leave_type' => $value->leave_type ?? Null,
                    'leave_duration' => $value->leave_duration ?? Null,
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

            //boa and dst staff list
            $userId = Leave::whereIn('status',[0,1,2])->pluck('user_id');
            $user   = User::whereIn('id',$userId)->whereIn('role',[7,8])->get(); 
            $array1 = [];
            $leave  = Null;
            $role   = Null;
            foreach($user as $key=>$value){

                $leave = Leave::where('user_id',$value->id)->orderBy('id','desc')->get();
                $role = Role::where('id',$value->role)->first();
                foreach($leave as $key=>$leaves){
                    $array1[] = array(
                        'id'      => $leaves->id ?? Null,
                        'user_id' => $value->id ?? Null,
                        'emp_name' =>$value->name ?? Null,
                        'emp_id'   =>$value->emp_id ?? Null,
                        'role'     =>$role->display_name ?? Null,
                        'leave_type' => $leaves->leave_type ?? Null,
                        'leave_duration' => $leaves->leave_duration ?? Null,
                        'from_date'  => $leaves->from_date ?? Null,
                        'to_date'    => $leaves->to_date ?? Null,
                        'remark'     => $leaves->remark ?? Null,
                        'applied_at' => $leaves->created_at ?? Null,
                        'status'     => $leaves->status ?? Null,
                       
                    );
                }
                
                
            }
            return response()->json(['status' => 'success', 'message' => 'Tsm and obst leave list.', 'data' => ['tsm_obst_leave_list' => $array,'oba_dst_leave_list'=>$array1,'pending_leave'=>$leaveCount]], 200);
        }
        elseif(Auth::user()->role == 9){
            $userslist =[];
            $rsm        = User::where('id',$request->rsm_id)->where('status',1)->first();
            if(!empty($rsm->area_id)){
                $userslist  = User::where('area_id', $rsm->area_id)->where('status',1)->whereNotIn('role',[1,2,4,7,8,9])->pluck('id')->toArray();
            }
            
            
            $leaveStaffList = Leave::whereIn('user_id',$userslist)->orderBy('id','desc')->get();
            $leaveCount = 0;
            $leaveCount = Leave::whereIn('user_id',$userslist)->where('status',0)->count();

            $array2 = [];
            $user  = Null;
            $role  = Null;
            foreach($leaveStaffList as $key=>$value){
                $user = User::where('status',1)->where('id',$value->user_id)->whereNotIn('role',[1,2,4,7,8,9])->first();
                $role = Role::where('id',$user->role)->first();
                $array2[] = array(
                    'id' => $value->id ?? Null,
                    'user_id' => $user->id ?? Null,
                    'emp_name' =>$user->name ?? Null,
                    'emp_id'   =>$user->emp_id ?? Null,
                    'role'     =>$role->display_name ?? Null,
                    'leave_type' => $value->leave_type ?? Null,
                    'leave_duration' => $value->leave_duration ?? Null,
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


            //boa and dst staff list
            $userId = Leave::whereIn('status',[0,1,2])->pluck('user_id');
            $user   = User::whereIn('id',$userId)->whereIn('role',[7,8])->get(); 
            $array1 = [];
            $leave  = Null;
            $role   = Null;
            foreach($user as $key=>$value){

                $leave = Leave::where('user_id',$value->id)->orderBy('id','desc')->get();
                $role = Role::where('id',$value->role)->first();
                foreach($leave as $key=>$leaves){
                    $array1[] = array(
                        'id'      => $leaves->id ?? Null,
                        'user_id' => $value->id ?? Null,
                        'emp_name' =>$value->name ?? Null,
                        'emp_id'   =>$value->emp_id ?? Null,
                        'role'     =>$role->display_name ?? Null,
                        'leave_type' => $leaves->leave_type ?? Null,
                        'leave_duration' => $leaves->leave_duration ?? Null,
                        'from_date'  => $leaves->from_date ?? Null,
                        'to_date'    => $leaves->to_date ?? Null,
                        'remark'     => $leaves->remark ?? Null,
                        'applied_at' => $leaves->created_at ?? Null,
                        'status'     => $leaves->status ?? Null,
                       
                    );
                }
                    
            }

            return response()->json(['status' => 'success', 'message' => 'Rsm leave list.', 'data' => ['rsm_leave_list' => $array2,'oba_dst_leave_list'=>$array1,'pending_leave'=>$leaveCount]], 200);
        }
    }

    //approve leave
    public function approveLeave(Request $request){
        if(Auth::user()->role == 6 || Auth::user()->role == 9){

            $leavebalance = LeaveBalance::where('user_id', $request->user_id)
                    ->where('year', Carbon::now()->format('Y')) 
                    ->first();

            $leaveApprove = Leave::where('id',$request->leave_id)->where('status',0)->first();
            $leaveApprove->status      = $request->status;
            
            //$leaveApprove->head_remark = $request->head_remark;
            $leaveApprove->approved_by = Auth::id();
            $leaveApprove->approved_at = date('Y-m-d');
            $leaveApprove->save();
            
            if($request->status ==1){

                //store approve leave Log
                $from  = Carbon::create(str_replace('/', '-', $leaveApprove->from_date))->format('Y-m-d');
                $to    = Carbon::create(str_replace('/', '-', $leaveApprove->to_date))->format('Y-m-d');
                $dates = $this->getBetweenDates($from, $to);
               
                $leaveCount = count($dates);
                //dd($count);
            if ($leavebalance) {

                if($leaveApprove->leave_duration == "Full Day") {

                    if($leaveApprove->leave_type == "PL") {
                        $leavebalance->paid_leaves = $leavebalance->paid_leaves - $leaveCount;
                        $leavebalance->leave_balance = $leavebalance->leave_balance - $leaveCount;  
                    }
                    if($leaveApprove->leave_type == "CL"){
                        $leavebalance->casual_leaves = $leavebalance->casual_leaves - $leaveCount;
                        $leavebalance->leave_balance = $leavebalance->leave_balance - $leaveCount;
                    }
                    if($leaveApprove->leave_type == "SL"){
                        $leavebalance->sick_leaves = $leavebalance->sick_leaves - $leaveCount;
                        $leavebalance->leave_balance = $leavebalance->leave_balance - $leaveCount;
                    }
                }

                $halfPaidleaves = $leavebalance->paid_leaves;
                $halfCasualleaves = $leavebalance->casual_leaves;
                $halfSickleaves = $leavebalance->sick_leaves;

                if($leaveApprove->leave_duration == "Second half"){

                    if($leaveApprove->leave_type == "PL") {

                        $leavebalance->paid_leaves = $halfPaidleaves - $leaveCount/2;
                        $leavebalance->leave_balance = $leavebalance->leave_balance - $leaveCount/2; 
                    }
                    if($leaveApprove->leave_type == "CL"){
                        $leavebalance->casual_leaves = $halfCasualleaves - $leaveCount/2;
                        $leavebalance->leave_balance = $leavebalance->leave_balance - $leaveCount/2;
                    }
                    if($leaveApprove->leave_type == "SL"){
                        $leavebalance->sick_leaves = $halfSickleaves - $leaveCount/2;
                        $leavebalance->leave_balance = $leavebalance->leave_balance - $leaveCount/2;
                    } 
                    
                }

                if($leaveApprove->leave_duration == "First half"){

                    if($leaveApprove->leave_type == "PL") {
                        $leavebalance->paid_leaves = $halfPaidleaves - $leaveCount/2;
                        $leavebalance->leave_balance = $leavebalance->leave_balance - $leaveCount/2; 
                    }
                    if($leaveApprove->leave_type == "CL"){
                        $leavebalance->casual_leaves = $halfCasualleaves - $leaveCount/2;
                        $leavebalance->leave_balance = $leavebalance->leave_balance - $leaveCount/2;
                    }
                    if($leaveApprove->leave_type == "SL"){
                        $leavebalance->sick_leaves = $halfSickleaves - $leaveCount/2;
                        $leavebalance->leave_balance = $leavebalance->leave_balance - $leaveCount/2;
                    } 
                }

                $leavebalance->save();
                
            } 
                foreach($dates as $key =>$value){
                    $leaveLog = new LeaveLog;
                    $leaveLog->leave_id   =$leaveApprove->id ?? Null;
                    $leaveLog->user_id    =$leaveApprove->user_id ?? Null;
                    $leaveLog->from_date  =$value ?? Null;
                    $leaveLog->leave_type =$leaveApprove->leave_type ?? Null;
                   $leaveLog->leave_duration =$leaveApprove->leave_duration ?? Null;
                    $leaveLog->remark     =$leaveApprove->remark ?? Null;
                    // $leaveLog->head_remark =$leaveApprove->head_remark ?? Null;
                    $leaveLog->status      =$leaveApprove->status ?? Null;
                    $leaveLog->created_by  =$leaveApprove->created_by ?? Null;
                    $leaveLog->created_by  =$leaveApprove->created_by ?? Null;
                    $leaveLog->approved_by =Auth::id() ?? Null;
                    $leaveLog->approved_at =date('Y-m-d') ?? Null;
                    $leaveLog->save();
                }
                // //send leave approve mail
                $staff      = User::where('id',$leaveApprove->user_id)->where('status',1)->first();
                $approvedBy = User::where('id',Auth::id())->where('status',1)->first();
                $to_mail    = $staff->email ?? Null;
                $leaveApproved =[
                    'employee_name'=> $staff->name,
                    'approved_by'=>$approvedBy->name,
                    'from_date' => $leaveApprove->from_date, 
                    'to_date' => $leaveApprove->to_date, 
                    'leave_type' => $leaveApprove->leave_type, 
                    'leave_status' => $leaveApprove->status == 1 ? 'Approved' : ($leaveApprove->status == 2 ? 'Rejected' : 'Pending')

                ];

                Mail::to($to_mail)->send(new ApproveLeaveMail($leaveApproved));

                return response()->json(['status' => 'success', 'message' => 'Leave approved successfully!'], 200);
            }else{
                
                $staff      = User::where('id',$leaveApprove->user_id)->where('status',1)->first();
                $approvedBy = User::where('id',Auth::id())->where('status',1)->first();
                $to_mail    = $staff->email ?? Null;
                $leaveApproved =[
                    'employee_name'=> $staff->name,
                    'approved_by'=>$approvedBy->name,
                    'from_date' => $leaveApprove->from_date, 
                    'to_date' => $leaveApprove->to_date, 
                    'leave_type' => $leaveApprove->leave_type, 
                    'leave_status' => $leaveApprove->status == 1 ? 'Approved' : ($leaveApprove->status == 2 ? 'Rejected' : 'Pending')

                ];

                Mail::to($to_mail)->send(new ApproveLeaveMail($leaveApproved));

                return response()->json(['status' => 'success', 'message' => 'Leave rejected successfully!'], 200);
            }
            
        }else{
            return response()->json(['status' => 'error', 'message' => "You do not have the authority to approve leave."], 400);
        }
    }

    //staff leave list
    public function staffLeaveList(Request $request){
        $users = Leave::where('user_id',$request->user_id)->orderBy('id','desc')->get();
        $leaveCount =0;
        $leaveCount =Leave::where('user_id',$request->user_id)->where('status',0)->count();

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
                'leave_type' => $value->leave_type ?? Null,
'leave_duration' => $value->leave_duration ?? Null,
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

        return response()->json(['status' => 'success', 'message' => 'Staff leave List!', 'data' => ['staff_leave_list'=>$array,'pending_leave'=>$leaveCount]], 200);
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

    //leave balance list
    public function leaveBalanceList(Request $request){

    $leave = LeaveBalance::where('user_id', $request->user_id)
                        // ->where('month', Carbon::now()->format('F')) 
                        ->where('year', Carbon::now()->format('Y')) 
                        ->first();

    $user   = User::where('status',1)->where('id',$request->user_id)->first();
    $role   = Role::where('id',$user->role)->first();

    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    $usedleaveCount = 0;

    // $leave_type = $request->leave_type ?? 'PL';
    $totalLeaves = Leave::where('user_id', $request->user_id)
    ->whereYear('from_date', $currentYear)
    ->whereIn('leave_type', ['PL', 'SL', 'CL'])
    ->where('status', 1)
    ->get()
    ->toArray();

    $leaveCount = 0; // Initialize the variable to store the total leave count
    $totalPaidLeaves=0;
    $totalSickLeaves=0;
    $totalCasualLeaves=0;

    foreach ($totalLeaves as $value) {
        $from = Carbon::create(str_replace('/', '-', $value['from_date']))->format('Y-m-d');
        $to = Carbon::create(str_replace('/', '-', $value['to_date']))->format('Y-m-d');
        $dates = $this->getBetweenDates($from, $to);

        if ($value['leave_duration'] == 'Full Day') {
            if ($value['leave_type'] == 'PL') {

                $totalPaidLeaves += count($dates);

            }elseif ($value['leave_type'] == 'SL') {

                $totalSickLeaves += count($dates);

            }elseif ($value['leave_type'] == 'CL') {

                $totalCasualLeaves += count($dates);
            }
        } else if ($value['leave_duration'] == 'First half' || $value['leave_duration'] == 'Second half') {
            if ($value['leave_type'] == 'PL') {

                $totalhalfdayCount = 0.5 * count($dates);
                $totalPaidLeaves += $totalhalfdayCount;

            }elseif ($value['leave_type'] == 'SL') {

                $totalhalfdayCount = 0.5 * count($dates);
                $totalSickLeaves += $totalhalfdayCount;

            }elseif ($value['leave_type'] == 'CL') {

                $totalhalfdayCount = 0.5 * count($dates);
                $totalCasualLeaves += $totalhalfdayCount;
            }
        }
    }
//     echo "<pre>";
//     print_r($totalPaidLeaves);
//     echo "</pre>";
//  exit();
//    dd($totalLeaves);

    
//     $totalPaidLeaves = $totalLeaves->where(['leave_type'=>'PL','status'=>1])->count();
//     //dd($totalPaidLeaves);
//     $totalSickLeaves = $totalLeaves->where(['leave_type'=>'SL','status'=>1])->count();
//     $totalCasualLeaves = $totalLeaves->where(['leave_type'=>'CL','status'=>1])->count();

 //dd($totalCasualLeaves);
    if ($leave) {
        $usedleaveCount = $leave->assigned_leaves - $leave->leave_balance;
    } else {
        $usedleaveCount = 0;
    }


    $leaveCount  =0;

    $designation = Designation::where('id',$user->designation)->first();

    if (is_object($leave) && !is_null($leave->date)) {
        $leave_date = date('d-m-Y',strtotime($leave->date));
    } else {
        $leave_date = null;
    }

    $array = array(
        'id'            =>$leave->id ?? Null,
        'user_id'       =>$request->user_id ?? Null,
        'emp_name'      =>$user->name ?? Null,
        'emp_id'        =>$user->emp_id ?? Null,
        'role'          =>$role->display_name ?? Null,
        'designation'   =>$designation->name ?? Null,
        'total_paid_leaves' => strval($leave && $totalPaidLeaves == 0 ? $leave->paid_leaves : $totalPaidLeaves + ($leave ? $leave->paid_leaves : 0)),
        'total_sick_leaves' => strval($leave && $totalSickLeaves == 0 ? $leave->sick_leaves : $totalSickLeaves + ($leave ? $leave->sick_leaves : 0)),
        'total_casual_leaves' => strval($leave && $totalCasualLeaves == 0 ? $leave->casual_leaves : $totalCasualLeaves + ($leave ? $leave->casual_leaves : 0)),
        'paid_leaves' =>!empty($leave->paid_leaves) ? $leave->paid_leaves : 0,
        'casual_leaves' =>!empty($leave->casual_leaves) ? $leave->casual_leaves : 0,
        'sick_leaves' =>!empty($leave->sick_leaves) ? $leave->sick_leaves : 0,
        'leave_balance' =>!empty($leave->leave_balance) ? $leave->leave_balance : 0,
        'leave_used'    =>$usedleaveCount ?? 0,
        'pending_leave' =>!empty($leave->leave_balance) ? $leave->leave_balance : 0,
        'assigned_leaves' =>!empty($leave->assigned_leaves) ? $leave->assigned_leaves : 0,
        'month'         =>$leave->month ?? Null,
        'year'          =>$leave->year ?? Null,
        'status'        =>$leave->status ?? Null,
        'system_status'        =>$leave->system_status ?? Null,
        'created_at'    =>$leave_date ?? Null,
        'no_of_privous_days'    =>7,
    );
    return response()->json(['status' => 'success', 'message' => 'Leave Balance List!', 'data' => ['leave_balance'=>$array]], 200);
}

}
