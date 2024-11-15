<?php

namespace App\Http\Controllers\Api\Attendance;

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
use App\Models\LeaveLog;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRegularization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Image;
use App\Models\Designation;
use App\Models\OutDoor;
use App\Models\Leave;

class ApiAttendanceController extends Controller
{
    /**
     * Store data attendance to DB
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function apiSaveAttendance(Request $request)
    {
        // Get all request
        $new = $request->all();

        // Get data setting
        $getSetting = Setting::find(1);

        // Get data from request
        $key = $new['key'];

        // Get user position
        $lat = $new['lat'];
        $longt = $new['longt'];


        $user = Auth::user();
        $areaId = $user->area_id;
        // $areaId = $new['area_id'];
        $q = $new['q'];
        // $WorkerId = $new['worker_id'];
        $WorkerId = $user->id;
        $device_id=$request->device_id;
        $status=$request->status;
        $role=$user->role;

        $date = Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d');

        if (!empty($key)) {
            if ($key == $getSetting->key_app) {

                // only obst role
                if ($role==3) {
                    // Check if user inside the area
       //Removed Lat Long Condition for KSPL Attendance App - 04/08/2023
                  /*
                    $branch_data = DB::table('location_coordinates')->where('area_id', $areaId)->get(['lat', 'long']);
                    log::debug('lnf_user_id:'.$WorkerId.', lnf_u_lat1: '.$lat.', lnf_u_long1: '.$longt.', lnf_l_lat2: '.$branch_data[0]->lat.', lnf_l_long2: '.$branch_data[0]->long);
                    $getPoly = Location::whereIn('area_id', [$areaId])->get(['lat', 'longt']);
                    if ($getPoly->count() == 0) {
                        $data = [
                            'message' => 'location not found',
                        ];
                        return response()->json($data);
                    }
                    */

                    // get data from location_coordinate table
                    // $branch_data = DB::table('location_coordinates')->where('area_id', $areaId)->get(['lat', 'long']);
                    // $isInside_radius = get_meters_between_points($lat, $longt, $branch_data['lat'], $branch_data['long']);
                    // if ($isInside_radius > 200) {
                    //     $data = [
                    //         'message' => 'cannot attend',
                    //     ];
                    //     return response()->json($data);
                    // }

                    // return $getPoly;

                   //Removed Lat Long Condition for KSPL Attendance App - 04/08/2023
                  /*
                    $isInside = $this->isInsidePolygon($lat, $longt, $getPoly);
                    if (!$isInside) {
                        $data = [
                            'message' => 'cannot attend',
                        ];
                        return response()->json($data);
                    }
                    */

                }

                // Check-in
                if ($q == 'in') {

                    // Get data from request
                    $in_time = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('H:i:s'));

                    // Check if user already check-in
                    $checkAlreadyCheckIn = Attendance::where('worker_id', $WorkerId)
                        ->where('date', Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d'))
                        ->where('in_time', '<>', null)
                        ->where('late_time', '<>', null)
                        ->where('out_time', null)
                        ->where('out_location_id', null)
                        ->first();

                    if ($checkAlreadyCheckIn) {
                        $data = [
                            'status' => 'error',
                            'message' => 'Already checked-in',
                        ];
                        return response()->json($data, 400);
                    }

                    // Get late time
                    $startHour = Carbon::createFromFormat('H:i:s', $getSetting->start_time);
                    if (!$in_time->gt($startHour)) {
                        $lateTime = "00:00:00";
                    } else {
                        $lateTime = $in_time->diff($startHour)->format('%H:%I:%S');
                    }

                    $location = Area::find($areaId)->name;


                    // add device id if not present in user table
                    $user_device=User::find($user->id);

                    if($user_device->device_id==null){
                        $user_device->device_id=$device_id;
                        $user_device->save();
                    }

                    // Save the data
                    $save = new Attendance();
                    $save->worker_id = $WorkerId;
                    $save->worker_device_id=$device_id;
                    $save->worker_role_id=$role;
                    $save->date = $date;
                    $save->status=5;
                    $save->in_location_id = $areaId;
                    $save->in_time = $in_time;
                    $save->late_time = $lateTime;
                    $save->in_lat_long = $lat.",".$longt;

                    $createNew = $save->save();

                    // Saving 
                    if ($createNew) {
                        $data = [
                            'status' => 'success',
                            'message' => 'Success!', 
                            'date' => Carbon::parse($date)->format('Y-m-d'),
                            'time' => Carbon::parse($in_time)->format('H:i:s'),
                            'location' => $location,
                            'query' => 'Check-in',
                        ];
                        return response()->json($data, 200);
                    }

                    $data = [
                        'status' => 'error',
                        'message' => 'Error! Something went wrong while Checking-in!',
                    ];
                    return response()->json($data, 400);
                }

                // Check-out
                if ($q == 'out') {
                    // Get data from request
                    $out_time = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('H:i:s'));
                    $getOutHour = new Carbon($getSetting->out_time);

                    // Get data in_time from DB
                    // To get data work hour
                    $getInTime = Attendance::where('worker_id', $WorkerId)
                        ->where('date', Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d'))
                        ->where('out_time', null)
                        ->where('out_location_id', null)
                        ->first();

                    if (!$getInTime) {
                        $data = [
                            'status' => 'error',
                            'message' => 'Check-in first',
                        ];
                        return response()->json($data, 400);
                    }

                    $in_time = Carbon::createFromFormat('H:i:s', $getInTime->in_time);

                    // Get data total working hour
                    $getWorkHour = $out_time->diff($in_time)->format('%H:%I:%S');

                    // Get over time
                    if ($in_time->gt($getOutHour) || !$out_time->gt($getOutHour)) {
                        $getOverTime = "00:00:00";
                    } else {
                        $getOverTime = $out_time->diff($getOutHour)->format('%H:%I:%S');
                    }

                    // Early out time
                    if ($in_time->gt($getOutHour)) {
                        $earlyOutTime = "00:00:00";
                    } else {
                        $earlyOutTime = $getOutHour->diff($out_time)->format('%H:%I:%S');
                    }

                    $location = Area::find($areaId)->name;

                    // Update the data
                    $getInTime->out_time = $out_time;
                    $getInTime->over_time = $getOverTime;
                    $getInTime->work_hour = $getWorkHour;
                    $getInTime->early_out_time = $earlyOutTime;
                    $getInTime->out_location_id = $areaId;

                    $updateData = $getInTime->save();

                    // Updating
                    if ($updateData) {
                        $data = [
                            'status' => 'success',
                            'message' => 'Success!',
                            'date' => Carbon::parse($date)->format('Y-m-d'),
                            'time' => Carbon::parse($out_time)->format('H:i:s'),
                            'location' => $location,
                            'query' => 'Check-Out',
                        ];
                        return response()->json($data, 200);
                    }
                    $data = [
                        'status' => 'error',
                        'message' => 'Error! Something Went wrong while Checking-out!',
                    ];
                    return response()->json($data, 400);
                }
                $data = [
                    'status' => 'error',
                    'message' => 'Error! Wrong Command!',
                ];
                return response()->json($data, 400);
            }
            $data = [
                'status' => 'error',
                'message' => 'The KEY is Wrong!',
            ];
            return response()->json($data, 400);
        }
        $data = [
            'status' => 'error',
            'message' => 'Please Setting KEY First!',
        ];
        return response()->json($data, 400);
    }

    // new attendence function using 200 meter radius
    public function apiSaveAttendanceNew(Request $request)
    {
        // $validator = Validator::make($request->all(), [
            //     'image' => 'required|image|mimes:jpeg,jpg,png',
        // ]);

        // if ($validator->fails()) {
            //     return response()->json(['errors'=>$validator->errors()->first()], 400);
        // }
        try{
            // Get all request
            $new = $request->all();

            // Get data setting
            $getSetting = Setting::find(1);

            // Get data from request
            $key = $new['key'];

            // Get user position
            $lat = $new['lat'];
            $longt = $new['longt'];


            $user = Auth::user();
            $areaId = $user->area_id;
            
            // $areaId = $new['area_id'];
            $q = $new['q'];
            // $WorkerId = $new['worker_id'];
            $WorkerId = $user->id;
            $device_id=$request->device_id;
            // $status=$request->status;
            $role=$user->role;

            $date = Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d');

            if (!empty($key)) {
                if ($key == $getSetting->key_app) {
                    if($user->status == 1){

                        // only obst role,boa and dst role
                        if ($role==3  || $role==5 || $role==6 || $role==7 || $role==8) {
                            // Check if user inside the area
                            // $getPoly = Location::whereIn('area_id', [$areaId])->get(['lat', 'longt']);
                            // if ($getPoly->count() == 0) {
                            //     $data = [
                            //         'message' => 'location not found',
                            //     ];
                            //     return response()->json($data);
                            // }

                           // get data from location_coordinate table

                            //Removed Lat Long Condition for KSPL Attendance App - 04/08/2023
                            /*
                            $branch_data = DB::table('location_coordinates')->where('area_id', $areaId)->get(['lat', 'long']);
                            // print_r($branch_data[0]->long);die;
                            log::debug('user_id:'.$WorkerId.', u_lat1: '.$lat.', u_long1: '.$longt.', l_lat2: '.$branch_data[0]->lat.', l_long2: '.$branch_data[0]->long);
                            $isInside_radius = $this->get_meters_between_points($lat, $longt, $branch_data[0]->lat, $branch_data[0]->long);
                            if($isInside_radius > 100) {
                                $data = [
                                    'message' => 'cannot attend',
                                ];
                                return response()->json($data);
                            }
                             */


                            // if ($isInside_radius > 100) {
                            //     $data = [
                            //         'message' => 'cannot attend',
                            //     ];
                            //     return response()->json($data);
                            // }

                            // return $getPoly;
                            // $isInside = $this->isInsidePolygon($lat, $longt, $getPoly);
                            // if (!$isInside) {
                            //     $data = [
                            //         'message' => 'cannot attend',
                            //     ];
                            //     return response()->json($data);
                            // }
                        }

                        // Check-in
                        if ($q == 'in') {

                            // Get data from request
                            $in_time = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('H:i:s'));

                            // Check if user already check-in
                            $checkAlreadyCheckIn = Attendance::where('worker_id', $WorkerId)
                                ->where('date', Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d'))
                                ->where('in_time', '<>', null)
                                ->where('late_time', '<>', null)
                                ->where('out_time', null)
                                ->where('out_location_id', null)
                                ->first();

                            if ($checkAlreadyCheckIn) {
                                $data = [
                                    'status' => 'error',
                                    'message' => 'Already Checked-in',
                                ];
                                return response()->json($data, 400);
                            }


                            
                            // Get late time
                            $startHour = Carbon::createFromFormat('H:i:s', $getSetting->start_time);
                            if (!$in_time->gt($startHour)) {
                                $lateTime = "00:00:00";
                            } else {
                                $lateTime = $in_time->diff($startHour)->format('%H:%I:%S');
                            }

                            $location = Area::find($areaId)->name;


                            // add device id if not present in user table
                            $user_device=User::find($user->id);

                            if($user_device->device_id==null){
                                $user_device->device_id=$device_id;
                                $user_device->save();
                            }

                            // Save the data
                            $save = new Attendance();
                            $save->worker_id = $WorkerId;
                            $save->worker_device_id=$device_id;
                            $save->worker_role_id=$role;
                            $save->date = $date;
                            //check in staff after 9:30am
                            // if(Carbon::now()->format('H:i:s') < '09:30:00'){
                            //     return response()->json(['status' => 'error', 'message' => 'Attendance can be marked after 9.30 A.M.', 'data' => []], 400);
                            // }else{

                                //if(Carbon::now()->format('H:i:s') < '10:10:00') {
                                    $save->status=1; //present
                                // }
                                // else{
                                //     $save->status=7;  //late
                                // }

                                // Remove time condition in Kspl Attendance App - 04-06-2023
                                /*
                                if ($role==3 || $role==5 || $role==6) {
                                //check status accoridng time lokesh code
                                if(Carbon::now()->format('H:i:s') < '10:16:00') {
                                    $save->status=1; //present
                                }elseif(Carbon::now()->format('H:i:s') >= '10:16:00' && Carbon::now()->format('H:i:s') < '10:31:00'){
                                    $save->status=7; //late
                                }else{
                                    $save->status=5; //absent
                                }
                                
                                }elseif($role==7 || $role==8){
                                    if(Carbon::now()->format('H:i:s') < '10:16:00') {
                                        $save->status=1; //present
                                    }
                                    elseif(Carbon::now()->format('H:i:s') >= '10:16:00'){
                                        $save->status=7; //late
                                    }
                                }
                                 */

                            //}
                            
                            $save->in_work_location = $request->in_work_location;
                            if(isset($request->in_work_location_remark)) {
                                $save->in_work_location_remark = $request->in_work_location_remark;
                            }
                            $save->in_location_id = $areaId;
                            $save->in_time = $in_time;
                            $save->late_time = $lateTime;
                            $save->in_lat_long = $lat.",".$longt;
                            //addimage
                            if ($request->hasFile('image')) {
                                    $image = $request->file('image');
                                    $input['image'] = time().'_'.$WorkerId.'.'.$image->getClientOriginalExtension();
                            
                                    $destinationPath = public_path('uploads/');
                                    $img = Image::make($image->getRealPath());
                                    $img->resize(100, 100, function ($constraint) {
                                            $constraint->aspectRatio();
                                    })->save($destinationPath.'/'.$input['image']);
                                                        
                                    $save->image = $input['image'];
                            }
                            //  else {
                            //         $save->{'image'} = 'default-user.png';
                            // }
                            
                            $createNew = $save->save();

                            // Saving
                            if ($createNew) {
                                $data = [
                                    'status' => 'success',
                                    'message' => 'Check-in Successful!',
                                    'date' => Carbon::parse($date)->format('Y-m-d'),
                                    'time' => Carbon::parse($in_time)->format('H:i:s'),
                                    'location' => $location,
                                    'in_work_location' => $request->in_work_location,
                                    'in_work_location_remark' => $request->in_work_location_remark,
                                    'query' => 'Check-in',
                                ];
                                return response()->json($data, 200);
                            }

                            $data = [
                                'status' => 'error',
                                'message' => 'Error! Something Went wrong while Checking-in!',
                            ];
                            return response()->json($data, 400);
                        }

                        // Check-out
                        if ($q == 'out') {
                            // Get data from request
                            $out_time = new Carbon(Carbon::now()->timezone($getSetting->timezone)->format('H:i:s'));
                            $getOutHour = new Carbon($getSetting->out_time);

                            // Get data in_time from DB
                            // To get data work hour
                            $getInTime = Attendance::where('worker_id', $WorkerId)
                                ->where('date', Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d'))
                                ->where('out_time', null)
                                ->where('out_location_id', null)
                                ->first();

                            if (!$getInTime) {
                                $data = [
                                    'status' => 'error',
                                    'message' => 'You need to Check-in first before Checking-out',
                                ];
                                return response()->json($data, 400);
                            }

                            $in_time = Carbon::createFromFormat('H:i:s', $getInTime->in_time);

                            // Get data total working hour
                            $getWorkHour = $out_time->diff($in_time)->format('%H:%I:%S');

                            // Get over time
                            if ($in_time->gt($getOutHour) || !$out_time->gt($getOutHour)) {
                                $getOverTime = "00:00:00";
                            } else {
                                $getOverTime = $out_time->diff($getOutHour)->format('%H:%I:%S');
                            }

                            // Early out time
                            $earlyOutTime='';
                            if ($in_time->gt($getOutHour)) {
                                $earlyOutTime = "00:00:00";
                            } else {
                                $earlyOutTime = $getOutHour->diff($out_time)->format('%H:%I:%S');
                            }

                            $location = Area::find($areaId)->name;

                            if ($getWorkHour < '07:00:00') {
                                $getInTime->status=11;  //Early Leave
                            }

                            // Update the data
                            $getInTime->out_time = $out_time;
                            $getInTime->over_time = $getOverTime;
                            $getInTime->work_hour = $getWorkHour;
                            $getInTime->early_out_time = $earlyOutTime;
                            $getInTime->out_location_id = $areaId;

                            $getInTime->out_work_location = $request->out_work_location;
                            if(isset($request->out_work_location_remark)) {
                                $getInTime->out_work_location_remark = $request->out_work_location_remark;
                            }

                            $updateData = $getInTime->save();

                            // Updating
                            if ($updateData) {
                                $data = [
                                    'status' => 'success',
                                    'message' => 'Check-out Successful!',
                                    'date' => Carbon::parse($date)->format('Y-m-d'),
                                    'time' => Carbon::parse($out_time)->format('H:i:s'),
                                    'location' => $location,
                                    'out_work_location' => $request->out_work_location,
                                    'out_work_location_remark' => $request->out_work_location_remark,
                                    'query' => 'Check-Out',
                                ];
                                return response()->json($data, 200);
                            }
                            $data = [
                                'status' => 'error',
                                'message' => 'Error! Something Went wrong while Checking-out!',
                            ];
                            return response()->json($data, 400);
                        }
                        $data = [
                            'status' => 'error',
                            'message' => 'Error! Wrong Command!',
                        ];
                        return response()->json($data, 400);
                    }
                    $data = [
                        'status' => 'error',
                        'message' => 'This account is deactivated!',
                    ];
                    return response()->json($data, 400);
                }
                $data = [
                    'status' => 'error',
                    'message' => 'The KEY is Wrong!',
                ];
                return response()->json($data, 400);
            }
            $data = [
                'status' => 'error',
                'message' => 'Please enter Setting KEY First!',
            ];
            return response()->json($data, 400);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went Wrong. Please try again later.'], 400);
        }
    }
    /**
     * Check if user inside the area
     * @param $x
     * @param $y
     * @param $polygon
     * @return \Illuminate\Http\Response
     */
    function get_meters_between_points($latitude1, $longitude1, $latitude2, $longitude2) {
        if (($latitude1 == $latitude2) && ($longitude1 == $longitude2)) { return 0; } // distance is zero because they're the same point
        $p1 = deg2rad($latitude1);
        $p2 = deg2rad($latitude2);
        $dp = deg2rad($latitude2 - $latitude1);
        $dl = deg2rad($longitude2 - $longitude1);
        $a = (sin($dp/2) * sin($dp/2)) + (cos($p1) * cos($p2) * sin($dl/2) * sin($dl/2));
        $c = 2 * atan2(sqrt($a),sqrt(1-$a));
        $r = 6371008; // Earth's average radius, in meters
        $d = $r * $c;
        return $d; // distance, in meters
    }

    public function isInsidePolygon($x, $y, $polygon)
    {
        $inside = false;
        for ($i = 0, $j = count($polygon) - 1, $iMax = count($polygon); $i < $iMax; $j = $i++) {
            $xi = $polygon[$i]['lat'];
            $yi = $polygon[$i]['longt'];
            $xj = $polygon[$j]['lat'];
            $yj = $polygon[$j]['longt'];

            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    public function getApprovePendingCount(Request $request)
    {
        try {
            $staffAttendanceCount = 0;
            if (Auth::user()->role == 5) {
                $tsmStaff = Auth::User()->staff->pluck('id')->toArray();
                $staffAttendanceCount = Attendance::whereIn('worker_id', $tsmStaff)->where('created_at', '>=', Carbon::today())->where('status', 5)->count();
            } elseif (Auth::user()->role == 6) {

                // Get todays date as well as yesters and day before yesterday's
                $tDate = Carbon::today()->format('Y-m-d');
                $yDate = Carbon::yesterday()->format('Y-m-d');
                $dBYdate = Carbon::today()->subDays(2)->format('Y-m-d');

                $mFirstDay = Carbon::now()->startOfMonth()->format('Y-m-d');
                $mSecondDay = Carbon::now()->startOfMonth()->addDay(1)->format('Y-m-d');

                $mLastDate = Carbon::now()->endOfMonth()->format('Y-m-d');

                $dateArray = [$tDate, $yDate, $dBYdate];

                if ($tDate == $mSecondDay) {
                    $dateArray = [$tDate, $yDate];
                } elseif ($tDate == $mFirstDay) {
                    $dateArray = [$tDate];
                }

                // Get tsm of rsm and then tsm's users from tsm_emps list as well as tsm's user list
                $tsmList = Auth::user()->rsmTsm->pluck('tsm_id')->toArray();
                $empIds = TsmEmp::whereIn('tsm_id', $tsmList)->orWhere('tsm_id', Auth::id())->pluck('emp_id')->toArray();
                $userslist = User::whereIn('id', $empIds)->where('role', 3)->pluck('id')->toArray();
                $staffAttendanceCount = Attendance::whereIn('worker_id', $userslist)->whereIn('date', $dateArray)->where('status', 5)->count();
            }
            return response()->json(['status' => 'success', 'message' => 'Attendance approve reject pending count.', 'data' => ['approve_pending_count' => $staffAttendanceCount]], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }
    }

    public function apiCheckinList(Request $request)
    {
        try {
            $staffAttendance = 0;
            $attendanceList=[];
            $absentStaff = [];
            // $tsmStaffAttendance = Null;
            if (Auth::user()->role == 5) {
                $tsmStaff = Auth::User()->staff->pluck('id')->toArray();
                $attendedStaff = Attendance::whereIn('worker_id', $tsmStaff)->where('created_at', '>=', Carbon::today())->pluck('worker_id')->toArray();
                // return $attendedStaff;
                $absentStaff = array_diff($tsmStaff, $attendedStaff);
                $staffAttendance = Attendance::whereIn('worker_id', $tsmStaff)->where('created_at', '>=', Carbon::today())->get();

                $staffAttendance->each(function ($item, $key) {
                    $user = User::where('status',1)->find($item->worker_id);
                    $item->worker_id = $user->emp_id;
                    $item->name = $user->name;
                });

                $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->get();
            } 
            elseif (Auth::user()->role == 6) {

                // Get todays date as well as yesters and day before yesterday's
                $tDate = Carbon::today()->format('Y-m-d');
                $yDate = Carbon::yesterday()->format('Y-m-d');
                $dBYdate = Carbon::today()->subDays(2)->format('Y-m-d');

                $mFirstDay = Carbon::now()->startOfMonth()->format('Y-m-d');
                $mSecondDay = Carbon::now()->startOfMonth()->addDay(1)->format('Y-m-d');

                $mLastDate = Carbon::now()->endOfMonth()->format('Y-m-d');

                $dateArray = [$tDate, $yDate, $dBYdate];

                if ($tDate == $mSecondDay) {
                    $dateArray = [$tDate, $yDate];
                } elseif ($tDate == $mFirstDay) {
                    $dateArray = [$tDate];
                }
              
                // Get tsm of rsm and then tsm's users from tsm_emps list as well as tsm's user list
                $tsmList   = Auth::user()->rsmTsm->pluck('tsm_id')->toArray();
                $empIds    = TsmEmp::whereIn('tsm_id', $tsmList)->orWhere('tsm_id', Auth::id())->pluck('emp_id')->toArray();
                $userslist = User::whereIn('id', $empIds)->where('status',1)->where('role', 3)->pluck('id')->toArray();
               
                $staffAttendance = Attendance::whereIn('worker_id', $userslist)->whereIn('date', $dateArray)->where('status', 5)->pluck('worker_id')->toArray();
                $absentStaff     = array_diff($userslist, $staffAttendance);

                $staffAttendance = Attendance::whereIn('worker_id', $userslist)->whereIn('date', $dateArray)->where('status', 5)->get();
                
                $staffAttendance->each(function ($item, $key) {
                    $user = User::where('status',1)->find($item->worker_id);
                    $item->worker_id = $user->emp_id;
                    $item->name = $user->name;

                    // $item->worker_id = User::find($item->worker_id)->emp_id;
                });

                $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->with('area')->get();
                // $attendanceList = Attendance::whereIn('worker_role_id',[3,5])->where('date', date('Y-m-d'))->get();
                // $array = [];
                // $name =Null;
                // $area =Null;
                // foreach($attendanceList as $key=>$value){
                //     $name = User::where('status',1)->where('id',$value->worker_id)->first();
                //     $area = Area::where('id',$value->in_location_id)->first();
                //     $array[] =array(
                //         'worker_id' =>$value->worker_id ?? Null,
                //         'status'    =>$value->status ?? Null,
                //         'name'      =>$name->name ?? Null,
                //         'emp_id'    =>$name->emp_id ?? Null,
                //         'branch_id' =>$value->in_location_id ?? Null,
                //         'branch_name'=>$area->address,
                //     );
                // }

            }
            return response()->json(['status' => 'success', 'message' => 'Check-in list of staff found.', 'data' => ['attendance' => $staffAttendance, 'absent' => $absentStaff]], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }
    }

    public function apiApproveAttendance(Request $request, Attendance $attendance)
    {
        //return $request->attendance;
        $validator = Validator::make($request->all(), [
            'status' => 'required|numeric',
        ]);

    
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message'=>$validator->errors()], 400);
        }
       
        try {
            if ($attendance->status == 5 ) {
                if (Auth::user()->role == 5) {
                    // Since TSM cann't accept attendance after 03:00 PM
                    if (Carbon::now()->format('H:i:s') > '15:00:00') {
                        return response()->json(['status' => 'error', 'message' => 'Attendance can not be approved since the time is past 03:00 PM', 'data' => []], 400);
                    }
                }

                if (Auth::user()->role == 6) {
                    // Since RSM cann't accept attendance after 11:59 PM
                    if (Carbon::now()->format('H:i:s') > '22:00:00') {
                        return response()->json(['status' => 'error', 'message' => 'Attendance can not be approved since the time is past 22:00 PM', 'data' => []], 400);
                    }
 
                }
                $attendance->status = $request->status;
                $attendance->status_updated_at = Carbon::now();
                $attendance->status_updated_by = Auth::id();
                $attendance->save();
                if ($request->status == 1) {
                    return response()->json(['status' => 'success', 'message' => 'Attendance accepted succefully.', 'data' => []], 200);
                } else {
                    return response()->json(['status' => 'success', 'message' => 'Attendance rejected succefully.', 'data' => []], 200);
                }
            }
            else {
                if ($attendance->status == 1) {
                    return response()->json(['status' => 'error', 'message' => 'Attendance accepted already.', 'data' => []], 200);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Attendance already rejected.', 'data' => []], 200);
                }
            }

        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }
    }

    public function markOT(Request $request, User $user)
    {
        try {
            DB::beginTransaction();
            if(Auth::user()->role == 6){
                if (Carbon::now()->format('H:i:s') > '23:59:00') {
                    return response()->json(['status' => 'error', 'message' => 'Attendance can not be approved since the time is past 23:59 PM', 'data' => []], 200);
                }
            } 
            // Since TSM cann't accept attendance after 03:00 PM
            if(Auth::user()->role == 6 || Auth::user()->role == 9){
                if (Carbon::now()->format('H:i:s') > '23:59:00') {
                    $attendance = new Attendance();
                    $attendance->worker_id = $user->id;
                    $attendance->worker_role_id = $user->role;
                    $attendance->date = Carbon::today()->format('Y-m-d');
                    $attendance->status = 3;
                    $attendance->status_updated_at = Carbon::now();
                    $attendance->status_updated_by = Auth::id();
                    $attendance->created_at = Carbon::now();
                    $attendance->updated_at = Carbon::now();
                    $attendance->save();
                    DB::commit();
                    $data['status'] = 'success';
                    $data['message'] = 'Attendance marked as OT Successfully.';
                    return response()->json($data, 200);
                } 
            }else{
                return response()->json(['status' => 'error', 'message' => 'Attendance can only approved by rsm and cmt', 'data' => []], 200);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }
    }

    // public function tsmCheckInStaff(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'reason' => 'required|max:500',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors'=>$validator->errors()], 400);
    //     }

    //     if (Auth::user()->role == 5 || Auth::user()->role == 6) {
    //         // Since TSM cann't accept attendance after 03:00 PM
    //         if (Carbon::now()->format('H:i:s') > '15:00:00') {
    //             $data['message'] = 'Attendance can not be approved since the time is past 03:00 PM.';
    //             return response()->json($data, 200);
               
    //         }
    //     }

    //     $staff = User::where('id',$request->staff_id)->where('role',3)->first();
    //     if(empty($staff)){
    //         $data['message'] = 'Staff id invalid!';
    //         return response()->json($data, 200);
    //     }

    //     $staff = User::where('id',$request->tsm_id)->where('role',5)->first();
    //     if(empty($staff)){
    //         $data['message'] = 'Tsm id invalid!';
    //         return response()->json($data, 200);
    //     }

    //     $checkAttdance = Attendance::where('worker_id',$request->staff_id)->where('status_updated_by',$request->tsm_id)->where('date',date('Y-m-d'))->where('status',1)->first();
    //     if(!empty($checkAttdance)){
    //         $data['message'] = 'Attendance marked already!';
    //         return response()->json($data, 200);
    //     }

    //     $area = TsmArea::where('tsm_id',$request->tsm_id)->first();
    //     //return $area;
    //     $attendance = new Attendance();
    //     $attendance->worker_id = $request->staff_id;
    //     $attendance->reason = $request->reason;
    //     $attendance->worker_device_id = $request->device_id;
    //     $attendance->worker_role_id = 3;
    //     $attendance->date = Carbon::today()->format('Y-m-d');
    //     $attendance->status = 1;
    //     $attendance->status_updated_at = Carbon::now();
    //     $attendance->status_updated_by = $request->tsm_id;
    //     $attendance->in_time = date('H:i:s');
    //     $attendance->in_location_id	 = $area->area_id;
    //     $attendance->created_at = Carbon::now();
    //     $attendance->updated_at = Carbon::now();
    //     $attendance->save();
    //     $data['message'] = 'Attendance marked successfully.';
    //     return response()->json($data, 200);
        
       
    // }

    //rsm check in staff
    public function tsmCheckInStaff(Request $request){
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' =>$validator->errors()], 400);
        }

        if(Auth::user()->role == 6){
            if (Carbon::now()->format('H:i:s') > '23:59:00') {
                return response()->json(['status' => 'error', 'message' => 'Attendance can not be approved since the time is past 23:59 PM', 'data' => []], 200);
            }
        } 
        if (Auth::user()->role == 6 || Auth::user()->role==9) {
            // Since RSM cann't accept attendance after 23:59 PM
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

            $staff = User::where('id',$request->rsm_id)->whereIn('role',[6,9])->first();
            if(empty($staff)){
                $data['status'] = 'error';
                $data['message'] = 'Rsm id invalid!';
                return response()->json($data, 400);
            }

            // $attendance = Attendance::where('id',$request->attendance_id)->where('worker_id',$request->staff_id)->first();
            // if(empty($attendance)){
            //     $data['message'] = 'Attendance id invalid!';
            //     return response()->json($data, 200);
            // }

            // $area = TsmArea::where('tsm_id',$request->rsm_id)->first();
            $staffArea = User::where('id',$request->staff_id)->first();
        
            if(!empty($staffArea->area_id)){
                $location = DB::table('location_coordinates')->where('area_id', $staffArea->area_id)->get(['lat', 'long']);
            }

            // $checkAttdanceStatus = Attendance::where('worker_id',$request->staff_id)->where('status_updated_by',$request->rsm_id)->where('date',date('Y-m-d'))->where('status',1)->first();
            // if(!empty($checkAttdanceStatus)){
            //     $data['message'] = 'Attendance marked already!';
            //     return response()->json($data, 200);
            // }
            //check attendance status 5 when update status
            
            // $obstOdCount = Attendance::where('worker_id',$request->staff_id)->where('worker_role_id',3)->whereMonth('date',date('m'))->where('status',3)->count();
            // if($obstOdCount >= 5){
            //     return response()->json(['status' => 'error', 'message' => 'Attendance can not be marked', 'data' => []], 200);
            // }else{
                
                //check already marked attendance
                $checkAllAttdance = Attendance::where('worker_id',$request->staff_id)->where('date',date('Y-m-d'))->whereIn('status',[1,3,4,6,7,8])->first();
                if(!empty($checkAllAttdance)){
                    $data['status'] = 'error';
                    $data['message'] = 'Attendance marked already.';
                    return response()->json($data, 400);
                }

                //approve absent staff attendance
                $checkAttdance = Attendance::where('worker_id',$request->staff_id)->where('date',date('Y-m-d'))->whereIn('status',[2,5])->first();
                if(!empty($checkAttdance)){
                    $checkAttdance->worker_id = $request->staff_id;
                    $checkAttdance->reason = $request->reason;
                    $checkAttdance->worker_device_id = $request->device_id;
                    $checkAttdance->worker_role_id = $staffArea->role;
                    $checkAttdance->date   = Carbon::today()->format('Y-m-d');
                    $checkAttdance->status = $request->status;
                    $checkAttdance->additional_status = $request->additional_status;
                    $checkAttdance->status_updated_at = Carbon::now();
                    $checkAttdance->status_updated_by = $request->rsm_id;
                    $checkAttdance->in_time         = date('H:i:s');
                    $checkAttdance->in_location_id	 = $staffArea->area_id;
                    $checkAttdance->created_at = Carbon::now();
                    $checkAttdance->updated_at = Carbon::now();
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
                    $attendance->date   = Carbon::today()->format('Y-m-d');
                    $attendance->status = $request->status;
                    $attendance->additional_status = $request->additional_status;
                    $attendance->status_updated_at = Carbon::now();
                    $attendance->status_updated_by = $request->rsm_id;
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
                
            // }
            
        }
          
    }

    //rsm branch list
    public function rsmBranchWiseList(Request $request){
      
        $user    = User::where('id',$request->rsm_id)->first();
        $rsmArea = TsmArea::where('tsm_id',$user->id)->get();
        $array = [];
        $area  =Null;
        foreach($rsmArea as $value){

            $area = Area::where('id',$value->area_id)->first();
                //count absent and late mark staff 
                $userslist       = User::where('area_id', $area->id)->pluck('id')->toArray();
                $staffAttendance = Attendance::where('in_location_id', $area->id)->where('date', date('Y-m-d'))->whereIn('status', [1,2,3,4,6,8])->pluck('worker_id')->toArray();
            
                //merge check in staff and absent staff
                $absentStaff     = array_diff($userslist,$staffAttendance);
                //get absent staff
                $count =0;
                $count = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,6,7,8,9])->count();
                
                //only tsm and obst count
                $userslist1 = User::where('area_id', $area->id)->whereIn('role',[3,5])->pluck('id')->toArray();
                $odPending=0;
                $odPending = OutDoor::whereIn('user_id',$userslist1)->where('status',0)->count();
                
                $leaveCount = 0;
                $leaveCount = Leave::whereIn('user_id',$userslist1)->where('status',0)->count();

                $pendingRegularizationCount = AttendanceRegularization::join('type_of_regularizations','type_of_regularizations.id','=','attendance_regularizations.regularization_id')
                ->where('attendance_regularizations.status',0)
                ->whereIn('attendance_regularizations.user_id' , $userslist1)
                ->count();

                $array[] =array(
                    'rsm_id'     =>$user->id ?? Null,
                    'branch_id'  =>$area->id ?? Null ,
                    'sole_id'    =>$area->name ?? Null,
                    'branch_name'=>$area->address ?? Null,
                    'rsm_staff_count'=>$count ?? 0,
                    'pending_od_count'=>$odPending  ?? 0,
                    'pending_regularization_count'=>$pendingRegularizationCount  ?? 0,
                    'pending_leave_count'=>$leaveCount ?? 0
                );
            }
            
        if ($user->area_id) {
            $area = Area::where('id',$user->area_id)->first();

                $userslist       = User::where('area_id', $area->id)->pluck('id')->toArray();
                $staffAttendance = Attendance::where('in_location_id', $area->id)->where('date', date('Y-m-d'))->whereIn('status', [1,2,3,4,6,8])->pluck('worker_id')->toArray();
                 //merge check in staff and absent staff
                $absentStaff     = array_diff($userslist,$staffAttendance);
                //get absent staff
                $count =0;
                $count = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,6,7,8,9])->count();
                
                //only tsm and obst count
                $userslist1 = User::where('area_id', $area->id)->whereIn('role',[3,5])->pluck('id')->toArray();
                $odPending=0;
                $odPending = OutDoor::whereIn('user_id',$userslist1)->where('status',0)->count();

                $leaveCount = 0;
                $leaveCount = Leave::whereIn('user_id',$userslist1)->where('status',0)->count();

                $pendingRegularizationCount = AttendanceRegularization::join('type_of_regularizations','type_of_regularizations.id','=','attendance_regularizations.regularization_id')
                ->where('attendance_regularizations.status',0)
                ->whereIn('attendance_regularizations.user_id' , $userslist1)
                ->count();

                $array[] =array(
                    'rsm_id'     =>$user->id ?? Null,
                    'branch_id'  =>$area->id ?? Null ,
                    'sole_id'    =>$area->name ?? Null,
                    'branch_name'=>$area->address ?? Null,
                    'rsm_staff_count'=>$count ?? 0,
                    'pending_od_count'=>$odPending  ?? 0,
                    'pending_leave_count'=>$leaveCount ?? 0,
                    'pending_regularization_count'=>$pendingRegularizationCount  ?? 0,
                );
            }
           
        $data['status'] = 'success';
        $data['message'] = 'Rsm area List.';
        $data['data']    =  $array;
        return response()->json($data, 200);
   
}

    //rsm tsm and obst staff list
    public function rsmTsmObstList(Request $request){
        // Get tsm of rsm and then tsm's users from tsm_emps list as well as tsm's user list
        if(Auth::user()->role == 6 || Auth::user()->role == 9){
            //check rsm branch
            $absentStaff = [];
            $lateStaff = [];
            $userslist =[];
            $sameBranchUsers =[];
            $staffAttendance =[];
            $tDate = Carbon::today()->format('Y-m-d');
            //get branch based rsm
            // $userRsm   = User::where('id',$request->rsm_id)->where('area_id',$request->branch_id)->first();
            $userslist = User::where('area_id',$request->branch_id)->where('status',1)->pluck('id')->toArray();
          
            //get rsm all area
            // $rsmArea = TsmArea::where('tsm_id',$userRsm->id)->pluck('area_id');
            // if(!empty($rsmArea)){
            //     $userslist       = User::whereIn('area_id', $rsmArea)->where('status',1)->pluck('id')->toArray();
            // }
            // //get all staff on rsm branch and same branch
            // $sameBranchUsers = User::where('area_id',$request->branch_id)->where('status',1)->pluck('id')->toArray();
            
            
            // $allstaff  = array_merge($sameBranchUsers,$userslist);
            //check tsm and obst attendance list
            $staffAttendance = Attendance::whereIn('worker_id', $userslist)->where('date', $tDate)->whereIn('status', [1,2,3,4,5,6,7,8])->pluck('worker_id')->toArray();
            //merge check in staff and absent staff
            $absentStaff     = array_diff($userslist,$staffAttendance);
            
            //get absent staff
            $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,6,7,8,9])->get();
            $array = [];
            $areas = Null;
            $attendances = Null;
            foreach($absentStaff as $key=>$value){
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
                 $attendance = Attendance::where('worker_id', $value->id)->where('date', $tDate)->whereNotIn('worker_role_id',[1,2,4,6,7,8,9])->where('status', 5)->first();
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
                        "in_work_location" => $attendance->in_work_location ?? Null,
                        "in_work_location_remark" => $attendance->in_work_location_remark ?? Null,
                        "out_work_location" => $attendance->out_work_location ?? Null,
                        "out_work_location_remark" => $attendance->out_work_location_remark ?? Null,
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

         
            //get late staff
            $lateStaffId = Attendance::whereIn('worker_id', $userslist)->where('date', $tDate)->whereIn('status', [5,7])->pluck('worker_id')->toArray();
            
            $lateStaff   = User::where('status',1)->whereIn('id', $lateStaffId)->whereNotIn('role',[1,2,4,6,7,8,9])->get();
            $array1 = [];
            $areas1 = Null;
            $attendances1 = Null;
            foreach($lateStaff as $key=>$value){

                
                //get areas
                 $area = Area::where('id',$value->area_id)->first();
                 $areas1 = array(
                    "id"=> $area->id,
                    "name"=> $area->name,
                    "address"=> $area->address,
                    "state"=> "",
                    "company_id"=> $area->company_id,
                    "created_at"=> $area->created_at,
                    "updated_at"=> $area->updated_at
                 ); 
                
                 
                 //get attendance
                 $attendance = Attendance::where('worker_id', $value->id)->where('date', $tDate)->whereNotIn('worker_role_id',[1,2,4,6,7,8,9])->whereIn('status', [5,7])->first();
                 if(!empty($attendance)){
                    $attendances1 = array(
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
                        "in_work_location" => $attendance->in_work_location ?? Null,
                        "in_work_location_remark" => $attendance->in_work_location_remark ?? Null,
                        "out_work_location" => $attendance->out_work_location ?? Null,
                        "out_work_location_remark" => $attendance->out_work_location_remark ?? Null,
                        "in_location_id"=> $attendance->in_location_id,
                        "in_lat_long"=> $attendance->in_lat_long,
                        "out_location_id"=> $attendance->out_location_id,
                        "out_lat_long"=> $attendance->out_lat_long,
                        "status"=> $attendance->status,
                        "additional_status"=> $attendance->additional_status ?? Null,
                        "status_updated_at"=> $attendance->status_updated_at,
                        "status_updated_by"=> $attendance->status_updated_by,
                        "reason"=> $attendance->reason,
                        "image"=> $attendance->image,
                        "created_at"=>$attendance->created_at,
                        "updated_at"=>$attendance->updated_at
                     ); 
                 }
                 
                $array1[] =array(
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
                    'area' =>$areas1,
                    'attendances' =>$attendances1,
                );
            }
        }
        
        //get tsm list based on branch
        if(Auth::user()->role ==6 || Auth::user()->role ==9){
            $tsmList=[];
            $areaList =[];
            //tsm list
            if(!empty($request->rsm_id)){
                $id      = $request->rsm_id;
                $tsm_ids = RsmTsm::where('rsm_id', $id)->pluck('tsm_id')->toArray();
                // Log::debug("==================================");
                // Log::debug($tsm_ids);
                $userslist =[];
                $staffAttendance =[];
                if(!empty($tsm_ids)){
                    // $userslist = User::whereIn('id', $tsm_ids)->where('status',1)->pluck('id')->toArray();
                    $userslist = User::whereIn('id', $tsm_ids)->where([['status',1],['role', 5]])->get();
                    // Log::debug("==================================");
                    // Log::debug($userslist);
                    
                    // $staffAttendance = Attendance::whereIn('worker_id', $userslist)->where('date', date('Y-m-d'))->whereIn('status', [1,2,3,4,5,6,7,8])->pluck('worker_id')->toArray();
                    // //merge check in staff and absent staff
                    // $absentStaff     = array_merge($userslist,$staffAttendance);
                    
                    // //get absent staff
                    // $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,6,7,8,9])->get();
                   
                    $areas = Null;
                    
                    foreach($userslist as $key => $value){
                        // Log::debug($value);
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
                        $attendancess = Null;
                        $attendance = Attendance::where('worker_id', $value->id)->where('date', date('Y-m-d'))->where('worker_role_id',5)->whereIn('status', [1,2,3,4,5,6,7,8])->first();
                        if(!empty($attendance)){
                            $attendancess = array(
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
                                "in_work_location" => $attendance->in_work_location ?? Null,
                                "in_work_location_remark" => $attendance->in_work_location_remark ?? Null,
                                "out_work_location" => $attendance->out_work_location ?? Null,
                                "out_work_location_remark" => $attendance->out_work_location_remark ?? Null,
                                "in_location_id"=> $attendance->in_location_id,
                                "in_lat_long"=> $attendance->in_lat_long,
                                "out_location_id"=> $attendance->out_location_id,
                                "out_lat_long"=> $attendance->out_lat_long,
                                "status"=> $attendance->status,
                                "additional_status"=> $attendance->additional_status ?? Null,
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
                            'attendances' =>$attendancess,
                        );
                    }  
                }
                
            }
            // if(!empty($request->rsm_id)){
            //     $id        = $request->rsm_id;
            //     $tsm_ids   = RsmTsm::where('rsm_id', $id)->pluck('tsm_id')->toArray();
            //     $rsm_area  = TsmArea::where('tsm_id',$id)->pluck('area_id')->toArray();
              
            //     $userslist =[];
            //     $staffAttendance =[];
            //     $usersarea =[];

                
            //     $usersarea  = User::whereIn('id',$tsm_ids)
            //                         ->where('role',5)
            //                         ->where('status',1)
            //                         ->pluck('area_id')
            //                         ->toArray(); 
                                   
            //     //check not existing tesm area                       
            //     $diff_area = array_diff($rsm_area,$usersarea);  
               
            //     //get area list
            //     $areas = Area::whereIn('id',$diff_area)->get();
            //     foreach($areas as $area){
            //         $areaList[] = array(
            //         "id"=> $area->id ?? Null,
            //         "name"=> $area->name ?? Null,
            //         "address"=> $area->address ?? Null,
            //         "state"=> "",
            //         "company_id"=> $area->company_id ?? Null,
            //         "created_at"=> $area->created_at ?? Null,
            //         "updated_at"=> $area->updated_at ?? Null
            //         ); 
            //     }

            // }
        }

        // Get TSM Areas
        $TsmAreas = TsmArea::where('tsm_id', $request->rsm_id)->pluck('area_id')->toArray();
        $userList = User::whereIN('area_id', $TsmAreas)->where('status',1)->pluck('id')->toArray();
        $empId    = TsmEmp::whereIn('emp_id', $userList)->where('tsm_id', '!=', $request->rsm_id)->pluck('emp_id')->toArray();
        // Log::debug($userList);
        // Log::debug($empId);
        // $emp_diff = implode(",", array_diff($userList, $empId));
        $emp_diff = array_diff($userList, $empId);

        // Log::debug($emp_diff);
        $areaList = Area::whereIN('id', User::whereIn('id', $emp_diff)->pluck('area_id')->toArray())->get();

        // Log::debug($areaList);
        $data['status'] = 'success';
        $data['message'] = 'All Staff List.';
        $data['image_url']=env('IMAGE_URL')."public/uploads/";
        $data['absent_staff']  =  $array ;
        $data['late_staff']    =  $array1 ;
        $data['tsm_list']       = $tsmList ?? [] ;
        $data['area_list']       = $areaList ?? [] ;
        return response()->json($data, 200);
    }
    
   //users lat long
   public function userLatLong(Request $request){
    $user = User::join('areas','areas.id','=','users.area_id')
                ->join('location_coordinates','location_coordinates.area_id','=','users.area_id')
                ->select('users.id as ID',
                'users.name as name',
                'users.image',
                'areas.address as branch_name',
                'location_coordinates.lat as lattitude',
                'location_coordinates.long as longitude',
                'users.home_latitude1',
                'users.home_longitude1',
                'users.home_latitude2',
                'users.home_longitude2',
                'location_coordinates.radius'
                )
                ->where('users.id',$request->user_id)
                ->where('status',1)
                ->first();
    $attendnaceMarked = Attendance::where('worker_id',$request->user_id)
                                  ->where('date',date('Y-m-d'))
                                  ->first();

  $in_time = NULL;
  $out_time = NULL;
                               
  if($attendnaceMarked){

    $in_time = $attendnaceMarked->in_time;
   $out_time = $attendnaceMarked->out_time;

    if($attendnaceMarked->in_time && !$attendnaceMarked->out_time){
        $status = 'YES';    
        }elseif($attendnaceMarked->in_time && $attendnaceMarked->out_time){
            $status = 'OUT';    
         }
        else{
            $status = 'NO';
        } 
   }else{
       $status = 'NO';
   }
   
    $data['status'] = 'success';
    $data['message'] = 'Deployed Branch List.';
    $array =Null;
    $array =array(
        'id'           =>$user->ID ?? Null,
        'name'         =>$user->name ?? Null,
        'branch_name'  =>$user->branch_name ?? Null,
        'latitude'     =>$user->lattitude ?? Null,
        'longitude'    =>$user->longitude ?? Null,
        'home_latitude1' =>$user->home_latitude1 ?? Null, 
        'home_longitude1'  =>$user->home_longitude1 ?? Null,
        'home_latitude2'  =>$user->home_latitude2 ?? Null,
        'home_longitude2'  =>$user->home_longitude2 ?? Null,
        'radius'    =>$user->radius ?? Null,
        'in_time' => $in_time, 
        'out_time' => $out_time,
        'check_in_status' =>$status,
        'face_recognize_status' => 1, //1 = true , 0 = false
        'profile_image' => isset($user->image) ? asset('uploads/'.$user->image) : ''

    );
    $data['users']    =  $array ;
    return response()->json($data, 200);
}

     //get tsm staff
    public function tsmstaffList(Request $request){
         //obst staff list tsm based
         $obst_List=[];
        if(!empty($request->tsm_id)){
            
            $tsm_id      = $request->tsm_id;
            $empId       = TsmEmp::where('tsm_id',$tsm_id)->pluck('emp_id')->toArray();
            
            // $userList =[];
            // $staffAttendance =[];
            if(!empty($empId)){
                // $userList    = User::whereIn('id',$empId)->where('status',1)->pluck('id')->toArray();
                
                // $staffAttendance = Attendance::whereIn('worker_id', $userList)->where('date', date('Y-m-d'))->whereIn('status', [1,2,3,4,5,6,7,8])->pluck('worker_id')->toArray();
                
                // //merge check in staff and absent staff
                // $absentStaff     = array_merge($userList,$staffAttendance);
                
                // //get absent staff
                // $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,6,7,8,9])->get();

                $date = date('Y-m-d');
                // DB::connection()->enableQueryLog();
                $obst_List = DB::select(
                    "SELECT U.id, U.name, U.role, U.emp_id, U.status AS 'user_status', B.name AS 'area_name', B.address, U.device_id,
                    A.date, A.in_time, A.out_time, A.in_location_id, A.in_lat_long, A.out_location_id, A.out_lat_long, A.additional_status, A.status, A.reason, A.image, A.status_updated_at, A.status_updated_by
                    FROM `users` AS U
                    LEFT JOIN ( 
                        SELECT worker_id, date, in_time, out_time, in_location_id, in_lat_long, out_location_id, out_lat_long, status, additional_status, status_updated_at, status_updated_by, reason, image 
                        FROM attendances 
                        WHERE `date` = '" .$date. "'
                    ) A ON `A`.`worker_id` = `U`.`id`
                    LEFT JOIN (
                        SELECT `id` as `area_id`, name, address 
                        FROM areas
                    ) `B` ON `U`.`area_id` = `B`.`area_id`
                    WHERE U.status = 1
                    AND U.role = 3
                    AND U.id IN (".implode(',',$empId).")"
                );
                // Log::debug(DB::getQueryLog());
                // $areas = Null;
                
                // foreach($absentStaff as $key=>$value){
                //     //get areas
                //     $area = Area::where('id',$value->area_id)->first();
                //     $areas = array(
                //         "id"=> $area->id ?? Null,
                //         "name"=> $area->name ?? Null,
                //         "address"=> $area->address ?? Null,
                //         "state"=> "",
                //         "company_id"=> $area->company_id ?? Null,
                //         "created_at"=> $area->created_at ?? Null,
                //         "updated_at"=> $area->updated_at ?? Null
                //     ); 
                //     $attendances = Null;
                //     //get attendance
                //     $attendance = Attendance::where('worker_id', $value->id)->where('date', date('Y-m-d'))->whereIn('worker_role_id',[3])->whereIn('status', [1,2,3,4,5,6,7,8])->first();
                //     if(!empty($attendance)){
                //         $attendances = array(
                //             "id"=> $attendance->id,
                //             "worker_id"=> $attendance->worker_id,
                //             "worker_role_id"=> $attendance->worker_role_id,
                //             "worker_device_id"=> $attendance->worker_device_id,
                //             "date"=> $attendance->date,
                //             "in_time"=> $attendance->in_time,
                //             "out_time"=> $attendance->out_time,
                //             "work_hour"=> $attendance->work_hour,
                //             "over_time"=> $attendance->over_time,
                //             "late_time"=>$attendance->late_time,
                //             "early_out_time"=> $attendance->early_out_time,
                //             "in_location_id"=> $attendance->in_location_id,
                //             "in_lat_long"=> $attendance->in_lat_long,
                //             "out_location_id"=> $attendance->out_location_id,
                //             "out_lat_long"=> $attendance->out_lat_long,
                //             "status"=> $attendance->status,
                //             "additional_status"=> $attendance->additional_status,
                //             "status_updated_at"=> $attendance->status_updated_at,
                //             "status_updated_by"=> $attendance->status_updated_by,
                //             "reason"=> $attendance->reason,
                //             "image"=> $attendance->image,
                //             "created_at"=>$attendance->created_at,
                //             "updated_at"=>$attendance->updated_at
                //         ); 
                //     }
                    
                //     $obst_List[] =array(
                //         "id"=> $value->id,
                //         "name"=> $value->name,
                //         "email"=> $value->email,
                //         "device_id"=> $value->device_id,
                //         "email_verified_at"=> $value->email_verified_at,
                //         "reset_token"=> $value->reset_token,
                //         "reset_token_expiry"=> $value->reset_token_expiry,
                //         "image"=>$value->image,
                //         "role"=>$value->role,
                //         "created_at"=> $value->created_at,
                //         "updated_at"=> $value->updated_at,
                //         "emp_id"=>$value->emp_id,
                //         "area_id"=> $value->area_id,
                //         "designation"=>$value->designation,
                //         "mobile_number"=>$value->mobile_number,
                //         "blood_group"=> $value->blood_group,
                //         "emergency_contact"=>$value->emergency_contact,
                //         "status"=> $value->status,
                //         "deactivated_by"=> $value->deactivated_by,
                //         "deactivated_at"=> $value->deactivated_at,
                //         "is_login"=> $value->is_login,
                //         'area' =>$areas,
                //         'attendances' =>$attendances,
                //     );
                // }  
            }
            
        }
        //obst staff list branch wise
        if(!empty($request->branch_id)){
        
            $branch_id   = $request->branch_id;
            
            $userList =[];
            $staffAttendance =[];

                // $rsm_area        = TsmArea::where('area_id',$branch_id)->pluck('tsm_id')->toArray();
                $userList        = User::where('area_id', $branch_id)->where('status',1)->pluck('id')->toArray();
                // log::debug($userList);
                $empId           = TsmEmp::whereIn('emp_id', $userList)->where('tsm_id', '!=', Auth::id())->pluck('emp_id')->toArray();
                // log::debug($empId);
                $emp_diff        = implode(",", array_diff($userList, $empId));
                // log::debug($emp_diff);
                // $staffAttendance = Attendance::whereIn('worker_id', $emp_diff)->where('worker_role_id',3)->where('date', date('Y-m-d'))->whereIn('status', [1,2,3,4,5,6,7,8])->pluck('worker_id')->toArray();
                
                //merge check in staff and absent staff
                // $absentStaff     = array_merge($emp_diff,$staffAttendance);
                // log::debug($absentStaff);
                //get absent staff
                // $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->where('role',3)->get();

                $date = date('Y-m-d');
                if(!empty($emp_diff)){
                    // $obst_List = DB::select(
                    //     "SELECT U.id, U.name, U.role, U.emp_id, U.status AS 'user_status', B.name AS 'area_name', B.address, U.device_id,
                    //     A.date, A.in_time, A.out_time, A.in_location_id, A.in_lat_long, A.out_location_id, A.out_lat_long, A.additional_status, A.status, A.reason, A.image, A.status_updated_at, A.status_updated_by
                    //     FROM `users` AS U
                    //     LEFT JOIN ( 
                    //         SELECT worker_id, date, in_time, out_time, in_location_id, in_lat_long, out_location_id, out_lat_long, status, additional_status, status_updated_at, status_updated_by, reason, image 
                    //         FROM attendances 
                    //         WHERE `date` = " .$date. "
                    //     ) A ON `A`.`worker_id` = `U`.`id`
                    //     LEFT JOIN (
                    //         SELECT `id` as `area_id`, name, address 
                    //         FROM areas
                    //         WHERE id = ".$branch_id."
                    //     ) `B` ON `U`.`area_id` = `B`.`area_id`
                    //     WHERE U.status = 1
                    //     AND U.role = 3
                    //     AND B.area_id = ".$branch_id."
                    //     AND U.id IN ($emp_diff)"
                    // );

                    $obst_List = DB::select(
                        "SELECT U.id, U.name, U.role, U.emp_id, U.status AS 'user_status', B.name AS 'area_name', B.address, U.device_id,
                        A.date, A.in_time, A.out_time, A.in_location_id, A.in_lat_long, A.out_location_id, A.out_lat_long, A.additional_status, A.status, A.reason, A.image, A.status_updated_at, A.status_updated_by
                        FROM `users` AS U
                        LEFT JOIN ( 
                            SELECT worker_id, date, in_time, out_time, in_location_id, in_lat_long, out_location_id, out_lat_long, status, additional_status, status_updated_at, status_updated_by, reason, image 
                            FROM attendances 
                            WHERE `date` = '" .$date. "'
                        ) A ON `A`.`worker_id` = `U`.`id`
                        LEFT JOIN (
                            SELECT `id` as `area_id`, name, address 
                            FROM areas
                        ) `B` ON `U`.`area_id` = `B`.`area_id`
                        WHERE U.status = 1
                        AND U.role = 3
                        AND U.id IN ($emp_diff)"
                    );
                }
                
                // $areas = Null;
              
                // $area = Area::where('id',$request->branch_id)->first();
                // $areas = array(
                //     // "id"=> $area->id ?? Null,
                //     "name"=> $area->name ?? Null,
                //     "address"=> $area->address ?? Null,
                //     // "state"=> "",
                //     // "company_id"=> $area->company_id ?? Null,
                //     // "created_at"=> $area->created_at ?? Null,
                //     // "updated_at"=> $area->updated_at ?? Null
                // ); 
                // foreach($absentStaff as $key=>$value){
                //     //get areas
                //     $attendances = Null;
                //     //get attendance
                //     $attendance = Attendance::where('worker_id', $value->id)->where('date', date('Y-m-d'))->where('worker_role_id',3)->whereIn('status', [1,2,3,4,5,6,7,8])->first();
                //     if(!empty($attendance)){
                //         $attendances = array(
                //             // "id"=> $attendance->id,
                //             // "worker_id"=> $attendance->worker_id,
                //             // "worker_role_id"=> $attendance->worker_role_id,
                //             // "worker_device_id"=> $attendance->worker_device_id,
                //             "date"=> $attendance->date,
                //             "in_time"=> $attendance->in_time,
                //             "out_time"=> $attendance->out_time,
                //             // "work_hour"=> $attendance->work_hour,
                //             // "over_time"=> $attendance->over_time,
                //             // "late_time"=>$attendance->late_time,
                //             // "early_out_time"=> $attendance->early_out_time,
                //             "in_location_id"=> $attendance->in_location_id,
                //             "in_lat_long"=> $attendance->in_lat_long,
                //             "out_location_id"=> $attendance->out_location_id,
                //             "out_lat_long"=> $attendance->out_lat_long,
                //             "status"=> $attendance->status,
                //             "additional_status"=> $attendance->additional_status,
                //             "status_updated_at"=> $attendance->status_updated_at,
                //             "status_updated_by"=> $attendance->status_updated_by,
                //             "reason"=> $attendance->reason,
                //             "image"=> $attendance->image,
                //             // "created_at"=>$attendance->created_at,
                //             // "updated_at"=>$attendance->updated_at
                //         ); 
                //     }
                    
                //     $obstList[] =array(
                //         "id"=> $value->id,
                //         "name"=> $value->name,
                //         // "email"=> $value->email,
                //         // "device_id"=> $value->device_id,
                //         // "email_verified_at"=> $value->email_verified_at,
                //         // "reset_token"=> $value->reset_token,
                //         // "reset_token_expiry"=> $value->reset_token_expiry,
                //         // "image"=>$value->image,
                //         "role"=>$value->role,
                //         // "created_at"=> $value->created_at,
                //         // "updated_at"=> $value->updated_at,
                //         "emp_id"=>$value->emp_id,
                //         // "area_id"=> $value->area_id,
                //         // "designation"=>$value->designation,
                //         // "mobile_number"=>$value->mobile_number,
                //         // "blood_group"=> $value->blood_group,
                //         "emergency_contact"=>$value->emergency_contact,
                //         "status"=> $value->status,
                //         // "deactivated_by"=> $value->deactivated_by,
                //         // "deactivated_at"=> $value->deactivated_at,
                //         // "is_login"=> $value->is_login,
                //         'area' =>$areas,
                //         'attendances' =>$attendances,
                //     );
                // }  
        }
        $data['status'] = 'success';
        $data['message'] = 'OBST Staff List.';
        $data['obst_staff']  =  $obst_List ;
        return response()->json($data, 200);
    }

    //rsm list
    public function rsmList(){
        try{
             $rsmList =[];
             $staffAttendance =[];
             $rsmList = User::where('role',6)->where('status',1)->pluck('id')->toArray();
             $staffAttendance = Attendance::whereIn('worker_id', $rsmList)->where('date', date('Y-m-d'))->whereIn('status', [1,2,3,4,5,6,7,8])->pluck('worker_id')->toArray();
             
              $absentStaff =[];
              //merge check in staff and absent staff
              $absentStaff     = array_merge($rsmList,$staffAttendance);
              
              //get absent staff
              $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,3,5,7,8,9])->get();
              
              $rsmList=[];
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
                  $attendances = Null;
                  $attendance = Attendance::where('worker_id', $value->id)->where('date', date('Y-m-d'))->whereIn('worker_role_id',[6])->whereIn('status', [1,2,3,4,5,6,7,8])->first();
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
                          "additional_status"=> $attendance->additional_status ?? Null,
                          "status_updated_at"=> $attendance->status_updated_at,
                          "status_updated_by"=> $attendance->status_updated_by,
                          "reason"=> $attendance->reason,
                          "image"=> $attendance->image,
                          "created_at"=>$attendance->created_at,
                          "updated_at"=>$attendance->updated_at
                      ); 
                  }
                  
                  $rsmList[] =array(
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
            $data['message'] = 'RSM List';
            $data['image_url']  =env('IMAGE_URL')."public/uploads/";
            $data['rsm_list'] = $rsmList;
            return response()->json($data, 200);
        } catch (Exception $e) {
              return response()->json(['status' => 'error','message'=>'Something Went Wrong!', 400]);
  
          }
    }

      //get tsm staff
      public function onlyTsmstaffList(Request $request){

         //obst staff
         if(!empty($request->tsm_id)){
           
            $tsm_id      = $request->tsm_id;
            $empId       = TsmEmp::where('tsm_id',$tsm_id)->pluck('emp_id')->toArray();
            
            $userList =[];
            $staffAttendance =[];
            $obstList=[];
            if(!empty($empId  )){
                $userList    = User::whereIn('id',$empId)->where('status',1)->pluck('id')->toArray();
                
                $staffAttendance = Attendance::whereIn('worker_id', $userList)->where('date', date('Y-m-d'))->whereIn('status', [1,2,3,4,5,6,7,8])->pluck('worker_id')->toArray();
                
                //merge check in staff and absent staff
                $absentStaff     = array_merge($userList,$staffAttendance);
                
                //get absent staff
                $absentStaff = User::where('status',1)->whereIn('id', $absentStaff)->whereNotIn('role',[1,2,4,6,7,8,9])->get();
                
                
                $areas = Null;
              
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
                    $attendances = Null;
                    //get attendance
                    $attendance = Attendance::where('worker_id', $value->id)->where('date', date('Y-m-d'))->whereIn('worker_role_id',[3])->whereIn('status', [1,2,3,4,5,6,7,8])->first();
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
                            "additional_status"=> $attendance->additional_status ?? Null,
                            "status_updated_at"=> $attendance->status_updated_at,
                            "status_updated_by"=> $attendance->status_updated_by,
                            "reason"=> $attendance->reason,
                            "image"=> $attendance->image,
                            "created_at"=>$attendance->created_at,
                            "updated_at"=>$attendance->updated_at
                        ); 
                    }
                    
                    $obstList[] =array(
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
        $data['message'] = 'OBST List';
        $data['obst_staff']        =  $obstList ;
        return response()->json($data, 200);
    }
    
}
