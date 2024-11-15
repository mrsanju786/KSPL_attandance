<?php

namespace App\Http\Controllers\Api\AttendanceRegularization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AttendanceRegularization;
use App\Models\RsmTsm;
use App\Models\User;
use App\Models\TypeOfRegularization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Mail\AttendanceRegularizationRequest;
use App\Mail\AttendanceRegularizationResponse;
use App\Models\Attendance;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use Auth;

class AttendanceRegularizationController extends Controller
{
    //
    public function attendanceMarkRequest(Request $request){
        $validator = Validator::make($request->all(), [
            'attendance_date' => 'required|date|before_or_equal:today',
            'in_time' => 'required',
            'type_of_regularization' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' =>$validator->errors()], 400);
        }

        if(AttendanceRegularization::where(['user_id'=>Auth::user()->id,'attendance_date'=>$request->attendance_date])->exists()){
            return response()->json(['status' => 'error', 'message' => 'You have already sent a request for this date.'], 400);
        }

        $rsmEmail = "";

        if(RsmTsm::where("tsm_id", Auth::user()->id)->doesntExist()){
            return response()->json(['status' => 'error', 'message' => 'Please assign a manager or RSM before submitting an attendance regularization request.'], 400);
        }else{
            $rsmEmail = User::where('id',RsmTsm::where("tsm_id", Auth::user()->id)->first()->rsm_id)->first()->email;
            
        }
        
        
        $data = new AttendanceRegularization;
        $data->user_id = Auth::user()->id;
        $data->regularization_id = $request->type_of_regularization;
        $data->attendance_date = $request->attendance_date;
        $data->in_time = $request->in_time;
        $data->out_time = $request->out_time;
        $data->reason = $request->reason;
        $data->created_by = Auth::user()->id;
        $data->save(); 

        $emailData = [
            'user_name' => Auth::user()->name,
            'attendance_date' => Carbon::parse($request->attendance_date)->format('d-m-Y'),
            'regularization_type' => TypeOfRegularization::where('id',$request->type_of_regularization)->first()->name ?? '-',
            'in_time' => Carbon::parse($request->in_time)->format('h:i A'),
            'out_time' =>  Carbon::parse($request->out_time)->format('h:i A'),
            'reason' => $request->reason,
        ];
        
        Mail::to($rsmEmail)->cc(env('MAIL_CCMAIL_USERNAME'))->send(new AttendanceRegularizationRequest($emailData));

        return response()->json(['status' => 'success', 'message' => 'Attendance mark request has been sent successfully.'], 200);
    }


    public function regularizationTypeOfList(){
        return response()->json(['status' => 'success', 'message' => 'Regularization Type of List', 'no_of_privous_days'=>7,'data'=>TypeOfRegularization::select('id','name')->get()], 200);
    }

    public function regularizationList(){

        $user = Auth::user();
        $data =[];

        $data['pending']= AttendanceRegularization::join('type_of_regularizations','type_of_regularizations.id','=','attendance_regularizations.regularization_id')
        ->where(['attendance_regularizations.user_id' => $user->id , 'attendance_regularizations.status'=>0])
        ->select(
            'attendance_regularizations.id',
            'type_of_regularizations.name as type_of_regularization',
            'attendance_date',
            'in_time',
            'out_time',
            'reason',
            'attendance_regularizations.created_at',
            'attendance_regularizations.status'
            )
        ->orderBy('attendance_regularizations.created_at','DESC')
        ->get()
        ->toArray();

        $data['approved']= AttendanceRegularization::join('type_of_regularizations','type_of_regularizations.id','=','attendance_regularizations.regularization_id')
        ->where(['attendance_regularizations.user_id' => $user->id , 'attendance_regularizations.status'=>1])
        ->select(
                'attendance_regularizations.id',
                'type_of_regularizations.name as type_of_regularization',
                'attendance_date',
                'in_time',
                'out_time',
                'reason',
                'attendance_regularizations.created_at',
                'attendance_regularizations.status'
        )
        ->orderBy('attendance_regularizations.created_at','DESC')
        ->get()
        ->toArray();

        return response()->json(['status' => 'success', 'message' => 'Regularization List','no_of_privous_days'=>7,'data'=> $data], 200);

    }

     //regularization staff list
     public function regularizationStaffList(Request $request){
       
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
            $regularizationStaffList = AttendanceRegularization::whereIn('user_id',$userslist)->orderBy('id','desc')->get();
            $regularizationCount = 0;
            $regularizationCount = AttendanceRegularization::whereIn('user_id',$userslist)->where('status',0)->count();

            $array = [];
            $user  = Null;
            $role  = Null;
            foreach($regularizationStaffList as $key=>$value){
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
                    'regularization_type'     => TypeOfRegularization::find($value->regularization_id)->name ?? Null,
                    'attendance_date'     => (string)$value->attendance_date ?? Null,
                    'in_time'  => $value->in_time ?? Null,
                    'out_time'    => $value->out_time ?? Null,
                    'reason'     => $value->reason ?? Null,
                    'status'     => $value->status ?? Null,
                    'created_at'      => $value->created_at ?? Null,
                );
            }

            //boa and dst staff list
            $userId = AttendanceRegularization::whereIn('status',[0,1,2])->pluck('user_id');
            $user   = User::whereIn('id',$userId)->whereIn('role',[7,8])->get(); 
            $array1 = [];
            $regularizations  = Null;
            $role   = Null;
            foreach($user as $key=>$value){

                $regularizations = AttendanceRegularization::where('user_id',$value->id)->orderBy('id','desc')->get();
                $role = Role::where('id',$value->role)->first();
                foreach($regularizations as $key=>$regularization){
                    $array1[] = array(
                        'id'      => $regularization->id ?? Null,
                        'user_id' => $value->id ?? Null,
                        'emp_name' =>$value->name ?? Null,
                        'emp_id'   =>$value->emp_id ?? Null,
                        'role'     =>$role->display_name ?? Null,
                        'regularization_type'     => TypeOfRegularization::find($regularization->regularization_id)->name ?? Null,
                        'attendance_date'     => (string)$regularization->attendance_date ?? Null,
                        'in_time'  => $regularization->in_time ?? Null,
                        'out_time'    => $regularization->out_time ?? Null,
                        'reason'     => $regularization->reason ?? Null,
                        'status'     => $regularization->status ?? Null,
                        'created_at' => $regularization->created_at ?? Null,
                    );
                }
                
                
            }

            
            return response()->json(['status' => 'success', 'message' => 'Tsm and obst regularization list.', 'data' => ['tsm_obst_regularization_list' => $array,'oba_dst_regularization_list'=>$array1,'pending_regularization'=>$regularizationCount]], 200);
        }
        elseif(Auth::user()->role == 9){
            $userslist =[];
            $rsm        = User::where('id',$request->rsm_id)->where('status',1)->first();
            if(!empty($rsm->area_id)){
                $userslist  = User::where('area_id', $rsm->area_id)->where('status',1)->whereNotIn('role',[1,2,4,7,8,9])->pluck('id')->toArray();
            }
            
            
            $leaveStaffList = AttendanceRegularization::whereIn('user_id',$userslist)->orderBy('id','desc')->get();
            $leaveCount = 0;
            $leaveCount = AttendanceRegularization::whereIn('user_id',$userslist)->where('status',0)->count();

            $regularizationStaffList = AttendanceRegularization::whereIn('user_id',$userslist)->orderBy('id','desc')->get();
            $regularizationCount = 0;
            $regularizationCount = AttendanceRegularization::whereIn('user_id',$userslist)->where('status',0)->count();

            $array2 = [];
            $user  = Null;
            $role  = Null;
            foreach($regularizationStaffList as $key=>$value){
                $user = User::where('status',1)->where('id',$value->user_id)->whereNotIn('role',[1,2,4,6,7,8,9])->first();
               
                if(!empty($user)){
                    $role = Role::where('id',$user->role)->first();
                }
                
                $array2[] = array(
                    'id' => $value->id ?? Null,
                    'user_id' => $user->id ?? Null,
                    'emp_name' =>$user->name ?? Null,
                    'emp_id'   =>$user->emp_id ?? Null,
                    'role'     =>$role->display_name ?? Null,
                    'regularization_type'     => TypeOfRegularization::find($value->regularization_id)->name ?? Null,
                    'attendance_date'     => (string)$value->attendance_date ?? Null,
                    'in_time'  => $value->in_time ?? Null,
                    'out_time'    => $value->out_time ?? Null,
                    'reason'     => $value->reason ?? Null,
                    'status'     => $value->status ?? Null,
                    'created_at'      => $value->created_at ?? Null,
                );
            }


            //boa and dst staff list
            $userId = AttendanceRegularization::whereIn('status',[0,1,2])->pluck('user_id');
            $user   = User::whereIn('id',$userId)->whereIn('role',[7,8])->get(); 
            $array1 = [];
            $leave  = Null;
            $role   = Null;
            foreach($user as $key=>$value){

                $regularizations = AttendanceRegularization::where('user_id',$value->id)->orderBy('id','desc')->get();
                $role = Role::where('id',$value->role)->first();
                foreach($regularizations as $key=>$regularization){
                    $array1[] = array(
                        'id'      => $regularization->id ?? Null,
                        'user_id' => $value->id ?? Null,
                        'emp_name' =>$value->name ?? Null,
                        'emp_id'   =>$value->emp_id ?? Null,
                        'role'     =>$role->display_name ?? Null,
                        'regularization_type'     => TypeOfRegularization::find($regularization->regularization_id)->name ?? Null,
                        'attendance_date'     => (string)$regularization->attendance_date ?? Null,
                        'in_time'  => $regularization->in_time ?? Null,
                        'out_time'    => $regularization->out_time ?? Null,
                        'reason'     => $regularization->reason ?? Null,
                        'status'     => $regularization->status ?? Null,
                        'created_at' => $regularization->created_at ?? Null,
                    );
                }
                    
            }

            return response()->json(['status' => 'success', 'message' => 'Rsm Regularization list.', 'data' => ['rsm_leave_list' => $array2,'oba_dst_leave_list'=>$array1,'pending_regularization'=>$regularizationCount]], 200);
        }
    }


    public function updateStatus($id,$status)
    {

        $attendanceRegularization = AttendanceRegularization::find($id);

        if ($attendanceRegularization) {
            // Update or create the attendance record
            $attendanceRegularization->status = ($status == "approved") ? 1 : 2;
            $attendanceRegularization->last_updated_by = Auth::user()->id;
            $attendanceRegularization->save();
            
            $attendance = Attendance::updateOrCreate(
                [
                    'worker_id' => $attendanceRegularization->user_id,
                    'date' => $attendanceRegularization->attendance_date,
                ],
                [
                    'worker_id' => $attendanceRegularization->user_id,
                    'worker_role_id' => $attendanceRegularization->user->userRole->id,
                    'date' => $attendanceRegularization->attendance_date,
                    'in_time' => $attendanceRegularization->in_time,
                    'out_time' => $attendanceRegularization->out_time,
                    'status' => ($status == "approved") ? 1 : 5,
                    'status_updated_by' => Auth::user()->id,
                ]
            );

                $emailData = [
                    'user_name' => $attendanceRegularization->user->name ?? '-',
                    'attendance_date' => Carbon::parse($attendanceRegularization->attendance_date)->format('d-m-Y'),
                    'regularization_type' => TypeOfRegularization::where('id',$attendanceRegularization->regularization_id)->first()->name ?? '-',
                    'in_time' => Carbon::parse($attendanceRegularization->in_time)->format('h:i A'),
                    'out_time' =>  Carbon::parse($attendanceRegularization->out_time)->format('h:i A'),
                    'status' => ($status == "approved") ? "Present" : "Absent",
                ];
                
                Mail::to($attendanceRegularization->user->email)->send(new AttendanceRegularizationResponse($emailData));
            // Return a success response
            return response()->json(['status' => 'success', 'message' => 'Status Updated Successfully.'], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Somthing went wrong.!!'], 400);
        }
    }
}
