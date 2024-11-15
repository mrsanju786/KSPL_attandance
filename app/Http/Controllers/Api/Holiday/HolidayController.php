<?php

namespace App\Http\Controllers\Api\Holiday;
use Auth;
use DateTime;
use Carbon\Carbon;
use App\Models\Area;
use App\Models\User;
use App\Models\Leave;
use App\Models\State;
use App\Models\Holiday;
use App\Models\TsmArea;
use App\Models\LeaveLog;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\AttendanceStatus;

class HolidayController extends Controller
{
    public function index(Request $request)
    {

        $data['status'] = 'success';
        $data['message'] = 'Holiday List';
        $tsm = TsmArea::where('area_id',$request->area_id)->pluck('tsm_id');
       
        $data['holidays'] = Holiday::whereIn('created_by',$tsm)->where('area_id','!=',Null)->get();
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|string',
            'name' => 'required',
            'area_id' =>'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message'=>$validator->errors()], 400);
        }

        try {
            $date = $request->date;

            $date = explode('-', $date);
            $from = Carbon::create(str_replace('/', '-', $date[0]))->format('Y-m-d');
            $to = Carbon::create(str_replace('/', '-', $date[1]))->format('Y-m-d');

            DB::beginTransaction();
            $holiday = new Holiday();
            $holiday->from_date = $from;
            $holiday->to_date = $to;
            $holiday->name = $request->name;
            $holiday->area_id = $request->area_id;
            $holiday->created_by = Auth::user()->id;
            $holiday->save();
            DB::commit();
            $data['status'] = 'success';
            $data['message'] = 'Holiday Added Updated Successfully!';
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }
    }

    public function updateStatus(Request $request, Holiday $holiday)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message'=>$validator->errors()], 400);
        }

        try {
            DB::beginTransaction();
            $holiday->status = $request->status;
            $holiday->approved_by = Auth::user()->id;
            $holiday->approved_at = Carbon::now();
            $holiday->updated_at = Carbon::now();
            $holiday->save();
            DB::commit();
            $data['status'] = 'success';
            $data['message'] = 'Holiday Status Updated Successfully!';
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 400);
        }
    }


    public function stateList(){
        $stateList = State::orderBy('id','asc')->get();
        return response()->json(['message'=>'success','data'=>$stateList]);
    }

    public function stateHoliday(Request $request){
        $state_id = $request->state_id;
        $holidays  = Holiday::where('state_id',$state_id)->orderBy('id','asc')->get();
        $array =[];
        foreach($holidays as $holiday){
            $array[]=array(
                'id' =>$holiday->id ?? Null,
                'name'=>$holiday->name ?? Null,
                'to' =>$holiday->date ?? Null,
                'from' =>$holiday->date ?? Null,
                'state'=>$holiday->state ?? Null
            );
        }

        return response()->json(['status' => 'success', 'message'=>'State Holiday List','data'=>$array]);
    }

    public function userHolidayAttendanceList(Request $request){

        $validator = Validator::make($request->all(), [
            'monthDate' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message'=>$validator->errors()], 400);
        }

        $userId     = Auth::user()->id;
        $userArea   = User::find($userId);
        $userState  = Area::where('id', $userArea->area_id)->first();
        // $absentArray = array();
       
        $userHolidays    = Holiday::where('state_id',$userState->state)->get()->toArray();
        $holidays = array();
        $holidaysDates = array();
        foreach ($userHolidays as $key => $item) {
            $status = 4;
            
            $holidays[$key] = [
                "name" => $item['name'],
                "from" => $item['date'], 
                "to" => $item['date'],
                "status" => $status ?? Null,
                "in_time" => Null,
                "out_time" => Null,
                "in_work_location" => Null,
                "in_work_location_remark" => Null,
                "out_work_location" => Null,
                'no_of_privous_days'    =>7,
                "out_work_location_remark" => Null
            ];

             // Create an array of leaves date to ignore those dates form attendace check to get only one status f
             foreach ($this->getBetweenDates($item['date'], $item['date']) as $key => $date) {
                $holidaysDates[] = array_push($holidaysDates, $date);
            }
        }
        
        $userLeaves  = Leave::where('user_id', $userId)->where('status', 1)->get()->toArray();
        $leaves = array();
        $leavesDates = array();
        foreach ($userLeaves as $key => $item) {
            $status = Null;
            if($item['status'] == 1){
                $status = 8;
            }
            $leaves[$key] = array(
                "name" => 'Paid Leave' ?? Null, // Replace test 1 with you input
                "from" => $item['from_date'] ?? Null, 
                "to"   => $item['to_date'] ?? Null,
                "status" => $status ?? Null,
                "in_time" => Null,
                "out_time" => Null,
                "in_work_location" => Null,
                "in_work_location_remark" => Null,
                "out_work_location" => Null,
                'no_of_privous_days'    =>7,
                "out_work_location_remark" => Null
            );
            // Create an array of leaves date to ignore those dates form attendace check to get only one status f
            foreach ($this->getBetweenDates($item['from_date'], $item['to_date']) as $key => $date) {
                $leavesDates[] = array_push($leavesDates, $date);
            }
        }
        
        $start_date = date('Y-m-01', strtotime($request->monthDate));
        $end_date = (date('Y-m-01') == $start_date) ? date('Y-m-d') : date('Y-m-t', strtotime($request->monthDate)); // For current month end date will be current date

        // Get dates array between start date and end date
        $dates = $this->getBetweenDates($start_date, $end_date);

        // Remove leaves dates from month dates To get attendance status of remaining dates
        $dates = array_diff($dates, $leavesDates,$holidaysDates);

        $i = 0;
        $dateArr = array_values($dates);
        $cnt     = count($dates);
        $attendance = array();
        for($i = 0; $i < $cnt; $i++){
            $staffAttendances = Attendance::where('worker_id', Auth::user()->id)->where('date', $dateArr[$i])->first();
          
            // foreach ($staffAttendances as $key => $item) {
                $attendanceStatus = Null;
                if(!empty($staffAttendances->status)){
                    $attendanceStatus = AttendanceStatus::where('id', $staffAttendances->status)->first();
                }
                //get week off
                $weekDay = date('w', strtotime($dateArr[$i]));
                $date =Null;
                $status =Null;
                $name   = Null;

                $in_time = NULL;
                $out_time = NULL;
                $in_work_location = NULL;
                $in_work_location_remark = NULL;
                $out_work_location = NULL;
                $out_work_location_remark = NULL;

                if(isset($staffAttendances)){
                $in_time = $staffAttendances->in_time;
                $out_time = $staffAttendances->out_time;
                $in_work_location = $staffAttendances->in_work_location;
                $in_work_location_remark = $staffAttendances->in_work_location_remark;
                $out_work_location = $staffAttendances->out_work_location;
                $out_work_location_remark = $staffAttendances->out_work_location_remark;
                }

                if($staffAttendances==null){
                    if($weekDay == 0 || $weekDay == 6){
                        $date   = $dateArr[$i];
                        $status = 6;
                        $name = 'Week Off';
                     }else{
                        $date   = $dateArr[$i];
                        $status = 5;
                        $name = 'Absent';
                     }
                }
                
                $attendance[] = [
                    "name"   => $attendanceStatus->name ?? $name,
                    "from"   => $dateArr[$i] ?? $date , 
                    "to"     => $dateArr[$i] ?? $date ,
                    "status" => $staffAttendances['status'] ?? $status,
                    "in_time" => $in_time,
                    "out_time" => $out_time,
                    "in_work_location" => $in_work_location,
                    "in_work_location_remark" => $in_work_location_remark,
                    "out_work_location" => $out_work_location,
                    'no_of_privous_days'    =>7,
                    "out_work_location_remark" => $out_work_location_remark
                ];
            // }
        }
       
        $absentArray = array_merge($holidays, $leaves, $attendance);
        
        return response()->json(['status' => 'success', 'message'=>'User Holiday List', "data" => $absentArray], 200);
    }
   
    public function userAttendanceList(Request $request){

        $validator = Validator::make($request->all(), [
            'userId' => 'required',
            'monthDate' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message'=>$validator->errors()], 400);
        }

        $userId     = $request->userId;
        $userArea   = User::find($userId);
        $userState  = Area::where('id', $userArea->area_id)->first();
        // $absentArray = array();
       
        $userHolidays    = Holiday::where('state_id',$userState->state)->get()->toArray();
        $holidays = array();
        $holidaysDates = array();
        foreach ($userHolidays as $key => $item) {
            $status = 4;
            
            $holidays[$key] = [
                "name" => $item['name'],
                "from" => $item['date'], 
                "to" => $item['date'],
                "status" => $status ?? Null,
                "in_time" => Null,
                "out_time" => Null,
                "in_work_location" => Null,
                "in_work_location_remark" => Null,
                "out_work_location" => Null,
                "out_work_location_remark" => Null
            ];

             // Create an array of leaves date to ignore those dates form attendace check to get only one status f
             foreach ($this->getBetweenDates($item['date'], $item['date']) as $key => $date) {
                $holidaysDates[] = array_push($holidaysDates, $date);
            }
        }
        
        $userLeaves  = Leave::where('user_id', $userId)->where('status', 1)->get()->toArray();
        $leaves = array();
        $leavesDates = array();
        foreach ($userLeaves as $key => $item) {
            $status = Null;
            if($item['status'] == 1){
                $status = 8;
            }
            $leaves[$key] = array(
                "name" => 'Paid Leave' ?? Null, // Replace test 1 with you input
                "from" => $item['from_date'] ?? Null, 
                "to"   => $item['to_date'] ?? Null,
                "status" => $status ?? Null,
                "in_time" => Null,
                "out_time" => Null,
                "in_work_location" => Null,
                "in_work_location_remark" => Null,
                "out_work_location" => Null,
                "out_work_location_remark" => Null
            );
            // Create an array of leaves date to ignore those dates form attendace check to get only one status f
            foreach ($this->getBetweenDates($item['from_date'], $item['to_date']) as $key => $date) {
                $leavesDates[] = array_push($leavesDates, $date);
            }
        }
        
        $start_date = date('Y-m-01', strtotime($request->monthDate));
        $end_date = (date('Y-m-01') == $start_date) ? date('Y-m-d') : date('Y-m-t', strtotime($request->monthDate)); // For current month end date will be current date

        // Get dates array between start date and end date
        $dates = $this->getBetweenDates($start_date, $end_date);

        // Remove leaves dates from month dates To get attendance status of remaining dates
        $dates = array_diff($dates, $leavesDates,$holidaysDates);

        $i = 0;
        $dateArr = array_values($dates);
        $cnt     = count($dates);
        $attendance = array();
        for($i = 0; $i < $cnt; $i++){
            $staffAttendances = Attendance::where('worker_id', $userId)->where('date', $dateArr[$i])->first();
          
            // foreach ($staffAttendances as $key => $item) {
                $attendanceStatus = Null;
                if(!empty($staffAttendances->status)){
                    $attendanceStatus = AttendanceStatus::where('id', $staffAttendances->status)->first();
                }
                //get week off
                $weekDay = date('w', strtotime($dateArr[$i]));
                $date =Null;
                $status =Null;
                $name   = Null;

                $in_time = NULL;
                $out_time = NULL;
                $in_work_location = NULL;
                $in_work_location_remark = NULL;
                $out_work_location = NULL;
                $out_work_location_remark = NULL;

                if(isset($staffAttendances)){
                $in_time = $staffAttendances->in_time;
                $out_time = $staffAttendances->out_time;
                $in_work_location = $staffAttendances->in_work_location;
                $in_work_location_remark = $staffAttendances->in_work_location_remark;
                $out_work_location = $staffAttendances->out_work_location;
                $out_work_location_remark = $staffAttendances->out_work_location_remark;
                }

                if($staffAttendances==null){
                    if($weekDay == 0 || $weekDay == 6){
                        $date   = $dateArr[$i];
                        $status = 6;
                        $name = 'Week Off';
                     }else{
                        $date   = $dateArr[$i];
                        $status = 5;
                        $name = 'Absent';
                     }
                }
                
                $attendance[] = [
                    "name"   => $attendanceStatus->name ?? $name,
                    "from"   => $dateArr[$i] ?? $date , 
                    "to"     => $dateArr[$i] ?? $date ,
                    "status" => $staffAttendances['status'] ?? $status,
                    "in_time" => $in_time,
                    "out_time" => $out_time,
                    "in_work_location" => $in_work_location,
                    "in_work_location_remark" => $in_work_location_remark,
                    "out_work_location" => $out_work_location,
                    "out_work_location_remark" => $out_work_location_remark
                ];
            // }
        }
       
        $absentArray = array_merge($holidays, $leaves, $attendance);
        
        return response()->json(['status' => 'success', 'message'=>'User Attendance List', "data" => $absentArray], 200);
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
