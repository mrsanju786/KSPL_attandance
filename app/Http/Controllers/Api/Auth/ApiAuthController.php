<?php

namespace App\Http\Controllers\Api\Auth;

use Auth;
use Response;
use App\Models\Area;
use App\Models\User;
use App\Models\Company;
use App\Models\Setting;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\UserDeviceIdLog;
use App\Models\RsmTsm;
use App\Models\TsmEmp;
use App\Models\NotificationList;

class ApiAuthController extends Controller
{
    public $successStatus = 200;

    /**
     * API data login
     *
     * @return \Illuminate\Http\Response
     */
    // public function login(Request $request)
    // {
    //     if (Auth::attempt(['email' => request('email'), 'password' => request('password')]) || Auth::attempt(['emp_id' => request('email'), 'password' => request('password')])) {
    //         $user = Auth::user();
    //         //check device id
    //         if($user->status ==1){
                
    //                 $user->device_id = $request->device_id;
    //                 $user->is_login = 1;
    //                 $user->save();
    //                 $data['message'] = "success";
    //                 $data['token'] = $user->createToken('nApp')->accessToken;
                    
    //                 // userlog start
    //                 $logdate = Carbon::now();
    //                 $userlog = new UserLog();
    //                 $userlog->user_id = $user->id;
    //                 $userlog->device_id  = $request->device_id;
    //                 $userlog->login_date = $logdate->toDateString();
    //                 $userlog->login_time = $logdate->toTimeString();
    //                 $userlog->save();
    //                 // userlog end

    //                 $user1 = User::find($user->id);
    //                 $user->designaton_name = $user->empDesignation->name;
    //                 $user->image = asset('uploads/'.$user->image);
    //                 $user->company_name = Company::where('id', Area::where('id', $user->area_id)->first()->company_id)->first()->company_name;
    //                 $user->blood_group = Null;
    //                 $data['user'] = $user;
    //                 $user = $user->area;
    //                 if ($user1->role == 5 || $user1->role == 6) {
    //                     $data['areas'] = $user1->areas;
    //                 } else {
    //                     $data['areas']  = [];
    //                 }

    //                 // id card signature image
    //                 $settings=Setting::all();
    //                 $first_value=$settings->first();
    //                 $data['id_card_signature']=asset('uploads/'.$first_value->id_card_signature);

    //                 return response()->json($data, $this->successStatus);
                        
                
          
    //         }
            
                
    //         $data['message'] = "This account is deactivated!";
    //         return response()->json($data);
    //     }

    //     $data['message'] = "Unauthorised";
    //     return response()->json($data);
    // }
    public function testfunction(Request $request)
    {
        return Auth::user();
    }

    public function logout(Request $request)
    {
        if($request->user()->token()){

            $request->user()->token()->revoke();
        }
        
        $user_id = Auth::user()->id;
        $user = User::where('id',$user_id)->first();
        $user->is_login = 0;
        $user->save();

        $logdate = Carbon::now();
        $logout_detail = UserLog::where('user_id', $user_id)->orderBy('id', 'desc')->first();
        if($logout_detail){
            $logout_detail->logout_date = $logdate->toDateString();
            $logout_detail->logout_time = $logdate->toTimeString();
            $logout_detail->logout_by = $user_id;
            $logout_detail->save();
        }

        return response()->json([
            'status' => 'success',
            'result' => true,
            'message' => 'Successfully logged out'
        ], 200);
    }

    public function apiFotgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message'=>$validator->errors()], 400);
            }

            $user = User::where('email', $request->email)->first();

            if ($user) {
                //Generate a random string.
                $token = openssl_random_pseudo_bytes(70);

                //Convert the binary data into hexadecimal representation.
                $token = bin2hex($token);

                $user->reset_token = $token;
                $user->reset_token_expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                $user->save();

                $url=url('reset_password/'.$user->reset_token.'/'. $user->email);

                Mail::to($user->email)->send(new ResetPasswordLink($user, $url));

                $data['status'] = "success";
                $data['message'] = "Forgot Password Link Sent! Please Check Your Mail.";
                return response()->json($data, 200);
            } else {
                $data['status'] = "error";
                $data['message'] = "User not found.";
                return response()->json($data, 400);
            }

        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }
    }

    public function apiChangePassword(Request $request){

        // Validate required inputs
        $validator = Validator::make($request->all(), [
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if($validator->fails()) {
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(["status"=>"error", "message"=>$errorString], 400);
        }

        try {
            // Fetch user details
            $user=User::find(Auth::user()->id);

            // Check users current password and password provided by user for extra layer of security.
            if(Hash::check($request->old_password, $user->password)){
                // Check wether new password is not same as previous
                if (Hash::check($request->new_password, $user->password)) {
                    return response()->json(['status'=>'error','message'=>'New password is same as old. Please try another password.'], 400);
                }else{
                    // Make has of new password and assigned to user
                    $user->password = Hash::make($request->new_password);
                }
            } else{
                // If users current and given password is not matched.
                return response()->json(['status'=>'error','message'=>'Wrong old password.'], 400);
            }
            // Save user with updated password
            $user->save();

            return response()->json(['status'=>'success','message'=>'Password updated successfully.'], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }

    }

    public function updateProfileImage(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|mimes:png,jpg,jpeg'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message'=>$validator->errors()], 400);
            }

            $user=User::find(Auth::user()->id);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                // image file name example: [news_id]_image.jpg
               // ${'image'} = $user->id. "" . "_image." . $file->getClientOriginalExtension();
                ${'image'} = $user->id . '_' . date('YmdHis') . "_image." . $file->getClientOriginalExtension();

                // save image to the path
                $file->move(Config::get('const.UPLOAD_PATH'), ${'image'});
                $user->{'image'} = ${'image'};
            } else {
                $user->{'image'} = 'default-user.png';
            }

            $user->save();
            return response()->json(['status'=>'success','message'=>'Profile Image Updated Successfully','data'=>$user], 200);
        }
        catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }

    }

    public function getProfile()
    {
        $data['status'] = "success";
        $data['message'] = "success";

        $user = Auth::user();


        $settings=Setting::all();
        $first_value=$settings->first();
        $data['id_card_signature']=asset('uploads/'.$first_value->id_card_signature);
        // id card signature image
        $user->image = asset('uploads/'.$user->image);
        $user->designaton_name = $user->empDesignation->name;
                    $user->company_name = Company::where('id', Area::where('id', $user->area_id)->first()->company_id)->first()->company_name;
                $data['data'] = $user;

        return response()->json($data, 200);
    }

    public function login(Request $request)
    {
        // Auth::attempt(['email' => request('email'), 'password' => request('password')]) ||
        if ( Auth::attempt(['emp_id' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            //check device id
            if($user->status==1){
                if(Auth::user()->role ==7 || Auth::user()->role ==8 || Auth::user()->role ==9){
                    if(Auth::user()->status ==1){
                        $user->device_id = $request->device_id;
                        $user->is_login = 1;
                        $user->save();

                        $data['status'] = "success";
                        $data['message'] = "success";
                        $data['token'] = $user->createToken('nApp')->accessToken;
                        
                        // userlog start
                        $logdate = Carbon::now();
                        $userlog = new UserLog();
                        $userlog->user_id = $user->id;
                        $userlog->device_id  = $request->device_id;
                        $userlog->login_date = $logdate->toDateString();
                        $userlog->login_time = $logdate->toTimeString();
                        $userlog->save();
                        // userlog end
    
                        $user1 = User::find($user->id);
                        $user->designaton_name = $user->empDesignation->name;
                        $user->image = asset('uploads/'.$user->image);
                        $user->company_name = Company::where('id', Area::where('id', $user->area_id)->first()->company_id)->first()->company_name;
                        $user->blood_group = Null;
                        $data['user'] = $user;
                        $user = $user->area;
                        if ($user1->role == 5 || $user1->role == 6) {
                            $data['areas'] = $user1->areas;
                        } else {
                            $data['areas']  = [];
                        }
    
                        // id card signature image
                        $settings=Setting::all();
                        $first_value=$settings->first();
                        $data['id_card_signature']=asset('uploads/'.$first_value->id_card_signature);
    
                        return response()->json($data, $this->successStatus);
                    }
                        
                }else{
                    if($user->status ==1){
                        $userDevices = User::where('status',1)->get();
                        $deviceIds = User::where('status',1)->whereNotIn('device_id',[Auth::user()->device_id])->pluck('device_id')->toArray();
                        foreach($userDevices as $device){
                            
                            if(in_array($request->device_id,$deviceIds)){
                                $name = User::where('device_id',$request->device_id)->first(); 
                                    
                                    $tsmstaff =Null;
                                    $tsmEmp   =Null;
                                    $rsmTsm   =Null;
                                    //store data in rsm staff
                                    $rsmStaff = User::where('email',Auth::user()->email)->first();
                                    if($rsmStaff->role ==6){
                                        //add rsm id
                                        $tsmstaff     = $rsmStaff->id;
                                        // $tsmEmp   = TsmEmp::where('emp_id',$rsmStaff->id)->first();
                                        $rsmTsm       = User::where('id',$rsmStaff->id)->where('role',6)->first();
                                        $notification = new NotificationList;
                                        $notification->user_id =$rsmStaff->id;
                                        $notification->rsm_id  =$rsmTsm->rsm_id ?? Null;
                                        $notification->message ='User trying to login '.$name->name.' device. You are not allowed to do this!!';
                                        $notification->save();
                                    }elseif($rsmStaff->role ==5){
                                    
                                        $tsmstaff = $rsmStaff->id;
                                        // $tsmEmp   = TsmEmp::where('emp_id',$rsmStaff->id)->first();
                                        // $rsmTsm   = RsmTsm::where('tsm_id',$rsmStaff->id)->first();
                                        $staff = User::where('id',$tsmstaff)->first();
                                        $rsm   = User::where('area_id',$staff->area_id)->where('role',6)->first();
                                        $notification = new NotificationList;
                                        $notification->user_id =$rsmStaff->id;
                                        $notification->rsm_id  =$rsm->id ?? Null;
                                        $notification->message ='User trying to login '.$name->name.' device. You are not allowed to do this!!';
                                        $notification->save();
                                    }else{
                                        $tsmstaff = $rsmStaff->id;
                                        // $tsmEmp   = TsmEmp::where('emp_id',$rsmStaff->id)->first();
                                        // $rsmTsm   = RsmTsm::where('tsm_id',$tsmEmp->tsm_id)->first();
                                        $staff = User::where('id',$tsmstaff)->first();
                                        $rsm   = User::where('area_id',$staff->area_id)->where('role',6)->first();
                                        $notification = new NotificationList;
                                        $notification->user_id =$rsmStaff->id;
                                        $notification->rsm_id  =$rsm->id ?? Null;
                                        $notification->message ='User trying to login '.$name->name.' device. You are not allowed to do this!!';
                                        $notification->save();
                                    }
                                    
        
                                return response()->json(['status' => 'error', 'message' => 'User trying to login '.$name->name.' device. You are not allowed to do this!!'], 400);
                                
                            }elseif(Auth::user()->device_id != $request->device_id){
                            
                                $tsmstaff =Null;
                                    $tsmEmp   =Null;
                                    $rsmTsm   =Null;
                                    //store data in rsm staff
                                    $rsmStaff = User::where('email',Auth::user()->email)->first();
                                    
                                    if($rsmStaff->role ==6){
                                        //add rsm id
                                        $tsmstaff = $rsmStaff->id;
                                        // $tsmEmp   = TsmEmp::where('emp_id',$rsmStaff->id)->first();
                                        $rsmTsm   = User::where('id',$rsmStaff->id)->where('role',6)->first();
                                        $notification = new NotificationList;
                                        $notification->user_id =$rsmStaff->id ?? Null;
                                        $notification->rsm_id  =$rsmTsm->rsm_id ?? Null;
                                        $notification->message ='User trying to login new device!!';
                                        $notification->save();
                                    }elseif($rsmStaff->role ==5){
                                    
                                        $tsmstaff = $rsmStaff->id;
                                        // $tsmEmp   = TsmEmp::where('emp_id',$rsmStaff->id)->first();
                                        // $rsmTsm   = RsmTsm::where('tsm_id',$rsmStaff->id)->first();
                                        $staff = User::where('id',$tsmstaff)->first();
                                        $rsm   = User::where('area_id',$staff->area_id)->where('role',6)->first();
                                        $notification = new NotificationList;
                                        $notification->user_id =$rsmStaff->id ?? Null;
                                        $notification->rsm_id  =$rsm->id ?? Null;
                                        $notification->message ='User trying to login new device!!';
                                        $notification->save();
                                    }else{
                                        $tsmstaff = $rsmStaff->id;
                                        // $tsmEmp   = TsmEmp::where('emp_id',$rsmStaff->id)->first();
                                        // $rsmTsm   = RsmTsm::where('tsm_id',$tsmEmp->tsm_id)->first();
                                        $staff = User::where('id',$tsmstaff)->first();
                                        $rsm   = User::where('area_id',$staff->area_id)->where('role',6)->first();
                                        $notification = new NotificationList;
                                        $notification->user_id =$rsmStaff->id ?? Null ;
                                        $notification->rsm_id  =$rsm->id ?? Null;
                                        $notification->message ='User trying to login new device!!';
                                        $notification->save();
                                    }
                                //store user device id logs 
                                $userDeviceId = User::where('id',Auth::user()->id)->first();
                                $userdeviceCount = UserDeviceIdLog::where('user_id',Auth::user()->id)->where('old_device_id',$request->device_id)->count();
                                if($userdeviceCount == 0){
                                    $userDeviceLog = new UserDeviceIdLog;
                                    $userDeviceLog->user_id = Auth::user()->id;
                                    $userDeviceLog->old_device_id = $userDeviceId->device_id;
                                    $userDeviceLog->new_device_id = $request->device_id;
                                    $userDeviceLog->date = date('Y-m-d');
                                    $userDeviceLog->save(); 
                                }
                                //update user new device id
                                User::where('id',$user->id)->update(['device_id'=>$request->device_id]);
        
                                $user->device_id = $request->device_id;
                                $user->is_login = 1;
                                $user->save();
                             
                                $data['status'] = "success";
                                $data['message'] = 'User trying to login new device!';
                                $data['token'] = $user->createToken('nApp')->accessToken;
                                
                                // userlog start
                                $logdate = Carbon::now();
                                $userlog = new UserLog();
                                $userlog->user_id = $user->id;
                                $userlog->device_id  = $request->device_id;
                                $userlog->login_date = $logdate->toDateString();
                                $userlog->login_time = $logdate->toTimeString();
                                $userlog->save();
                                // userlog end
            
                                $user1 = User::find($user->id);
                                $user->designaton_name = !empty($user->empDesignation->name) ? $user->empDesignation->name : Null;
                                $user->image = asset('uploads/'.$user->image);
                                $user->company_name = Company::where('id', Area::where('id', $user->area_id)->first()->company_id)->first()->company_name;
                                $user->blood_group = Null;
                                
                                $data['user'] = $user;
                                $user = $user->area;
                                if ($user1->role == 5 || $user1->role == 6) {
                                    $data['areas'] = $user1->areas;
                                } else {
                                    $data['areas']  = [];
                                }
            
                                // id card signature image
                                $settings=Setting::all();
                                $first_value=$settings->first();
                                $data['id_card_signature']=asset('uploads/'.$first_value->id_card_signature);
                          
                                return response()->json($data, $this->successStatus);
                                
                            }else{
                                
                                $user->device_id = $request->device_id;
                                $user->is_login = 1;
                                $user->save();
                                $data['status'] = "success";
                                $data['message'] = "success";
                                $data['token'] = $user->createToken('nApp')->accessToken;
                                
                                // userlog start
                                $logdate = Carbon::now();
                                $userlog = new UserLog();
                                $userlog->user_id = $user->id;
                                $userlog->device_id  = $request->device_id;
                                $userlog->login_date = $logdate->toDateString();
                                $userlog->login_time = $logdate->toTimeString();
                                $userlog->save();
                                // userlog end
            
                                $user1 = User::find($user->id);
                                $user->designaton_name = $user->empDesignation->name;
                                $user->image = asset('uploads/'.$user->image);
                                $user->company_name = Company::where('id', Area::where('id', $user->area_id)->first()->company_id)->first()->company_name;
                                $user->blood_group = Null;
                                $data['user'] = $user;
                                $user = $user->area;
                                if ($user1->role == 5 || $user1->role == 6) {
                                    $data['areas'] = $user1->areas;
                                } else {
                                    $data['areas']  = [];
                                }
            
                                // id card signature image
                                $settings=Setting::all();
                                $first_value=$settings->first();
                                $data['id_card_signature']=asset('uploads/'.$first_value->id_card_signature);
            
                                return response()->json($data, $this->successStatus);
                                
                            }
                        }
                    }
                }
            
            }
           
            return response()->json(['status' => 'error', 'message' => 'This account is deactivated!'], 400);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid Login Credentials'], 400);

    }

    
    //user notification List
    public function notificationList(Request $request){
                if(Auth::user()->role ==6){
            $notificationList =NotificationList::where('rsm_id',Auth::id())->orderBy('id','desc')->get();
            $array = [];
            $user = Null;
            $area = Null;
            foreach($notificationList as $key=>$value){
                $user = User::where('id',$value->user_id)->where('role','!=',6)->first();
                                   $area = Area::where('id',$user->area_id)->first();
                                $array[] = array(
    
                    'id'          =>$value->id,
                    'user_id'     =>$value->user_id ?? Null,
                    'emp_id'      =>$user->emp_id ?? Null,
                    'name'        =>$user->name ?? Null,
                    'branch_id'   =>$area->id ?? Null,
                    'branch_name' =>$area->address ?? Null,
                    'message'     =>$value->message ?? Null,
                    'created_at'  =>$value->created_at ?? Null,
                );
            }
    
        }elseif(Auth::user()->role ==9){
            $notificationList =NotificationList::orderBy('id','desc')->get();
            $array = [];
            $user = Null;
            $area = Null;
            foreach($notificationList as $key=>$value){
                $user = User::where('id',$value->user_id)->first();
                                    $area = Area::where('id',$user->area_id)->first();
                                $array[] = array(
    
                    'id'          =>$value->id,
                    'user_id'     =>$value->user_id ?? Null,
                    'emp_id'      =>$user->emp_id ?? Null,
                    'name'        =>$user->name ?? Null,
                    'branch_id'   =>$area->id ?? Null,
                    'branch_name' =>$area->address ?? Null,
                    'message'     =>$value->message ?? Null,
                    'created_at'  =>$value->created_at ?? Null,
                );
            }
        }
        
        $data['status'] = 'success';
        $data['message'] = 'Notification List.';
        $data['data']    =  $array;
        return response()->json($data, 200);
    }

}
