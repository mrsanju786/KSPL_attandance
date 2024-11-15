<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Attendance;
use App\Models\History;
use App\Models\Setting;
use App\Models\User;
use App\Models\ActivityLog;
use Carbon\Carbon;
use DB;
use Excel;
use App\Exports\EmployeeNotLoggedOneWeek;
use App\Exports\EmployeeNotLoggedTillDate;
use Yajra\Datatables\Datatables;
use App\Models\UserLog;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    // public function index(Request $request)
    // {
    //     // Get start time to check late worker
    //     $getSetting = Setting::find(1);

    //     // Get all data for summary
    //     $userCount = User::count();
    //     $attendaceToday = Attendance::where('date', Carbon::now()->format('Y-m-d'))->where('status',1)->count();
    //     $attendanceLateToday = Attendance::where('date', Carbon::now()->format('Y-m-d'))
    //         ->where('in_time', '>', $getSetting->start_time)
    //         ->count();
    //     $areaCount   = Area::count();
    //     $absentToday = Attendance::where('date', Carbon::now()->format('Y-m-d'))->whereIn('status',[0,5])->count();
    //     $NTA = Attendance::where('date', Carbon::now()->format('Y-m-d'))->whereIn('status',[5])->count();
    //     $PresentPending = $attendaceToday+$NTA;
    //     $absentTodayNew = round($userCount - $PresentPending);
        
    //     $user = User::where('role',3)->pluck('area_id');
    //     $branchName   = Area::whereIn('id',$user)->whereNotIn('id',[865,866])->orderBy('address','asc')->get();
    //     $notLoggedInEmployee  = User::with('area')->whereNull('device_id')->whereNotIn('role',[1,2])->where('status',1)->orderBy('id','desc')->get();
    //     //return $notLoggedInEmployee;
       
    //     $loggedUser  = UserLog::whereBetween('login_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->pluck('user_id');
    //     $oneWeekEmployee = User::with('area')->whereNotIn('id',$loggedUser)->whereNotIn('role',[1,2])->where('status',1)->orderBy('id','desc')->get();
    //     //bar chart based on current month
    //     $month=$request->month;
    //     if(!empty($month)){
    //         $date = date($month);
    //     }else{
    //         $date = date('m');
    //     }

    //     $years=$request->year;
        
    //     if(!empty($years)){
    //         $year = date($years);
    //     }else{
    //         $year = date('Y');
    //     }
      
    //     $area    =Null;
    //     $areas   = Area::whereIn('id',$user)->whereNotIn('id',[865,866])->orderBy('address','asc')->first();
    //     $branch = $request->branch;
    //     if(!empty($branch)){
    //       $area = $branch;
    //     }else{
    //       $area = $areas->id;
    //     }
       
    //     $today = today(); 
    //     $dates = []; 

    //     // for($i=1; $i < $today->daysInMonth + 1; ++$i) {
    //     //     $dates[] = \Carbon\Carbon::createFromDate($today->year, $today->month, $i)->format('d');
    //     // }
    //     $result = Attendance::whereMonth('date',$date)->whereYear('date',$year)->where('in_location_id',$area)->select("date", "status",'in_location_id')->groupBy('date')->orderBy('date','asc')->get();
        
        

    //     //return $result;
    //     //$year = date('Y');
    //    // $lastmonth = date("m",strtotime("-1 month"));
    // //    $d=cal_days_in_month(CAL_GREGORIAN,$date,$year);
    // //    $days = []; 
    // //    for($i =1;$i<=$d;$i++){
    // //      $days[] = $i;
         
    // //      //$i++;
    // //     }
        
           
       
    //    //return $result;
    //  //  return $day;
    //    //$result =Null;
    //     // if($branch){
            

    //        // return $result;
    //         // dd($result);
    //         // if(count($result)==0){
    //         //     $lastmonth = date("m",strtotime("-1 month"));
    //         //     $lastyear = date("Y", strtotime("-1 year"));
    //         //     $result = Attendance::whereMonth('date',$lastmonth)->whereYear('date',$lastyear)->where('in_location_id',$area)->select("date", "status",'in_location_id')->groupBy('date')->orderBy('date','desc')->get();
    //         // }
    //    // }
    //     // elseif($date){
    //     //     $result = Attendance::whereMonth('date',$date)->whereYear('date',$year)->where('in_location_id',$area)->select("date", "status")->groupBy('date')->orderBy('date','asc')->get();
    //     //    // return $result;
    //     // }else{
    //     //     $result = Attendance::select("date", "status")->groupBy('date')->orderBy('date','asc')->get();
    //     // }

      
    //     return view('home', compact('userCount', 'attendaceToday', 'attendanceLateToday', 'areaCount','absentToday','absentTodayNew','NTA','branchName','notLoggedInEmployee','oneWeekEmployee','result','branch','month','years','area'));
    // }
    public function index(Request $request)
    {
        // Get start time to check late worker
        $getSetting = Setting::find(1);

        // Get all data for summary
        $userCount      = User::count();
        $activeUser     = User::where('status',1)->count();
        $deactiveUser   = User::where('status',0)->count();
        $attendaceToday = Attendance::where('date', Carbon::now()->format('Y-m-d'))->whereIn('status',[1,3,4,5,6,7,8])->count();
        $activeUserCount      = User::where('status',1)->count();
        $absentTodayNew = round($activeUserCount - $attendaceToday);
        
        // $attendaceToday = Attendance::where('date', Carbon::now()->format('Y-m-d'))->where('status',1)->count();
        // $attendanceLateToday = Attendance::where('date', Carbon::now()->format('Y-m-d'))
        //     ->where('in_time', '>', $getSetting->start_time)
        //     ->count();
        
        
        // $absentToday = Attendance::where('date', Carbon::now()->format('Y-m-d'))->whereIn('status',[0,5])->count();
        // $NTA = Attendance::where('date', Carbon::now()->format('Y-m-d'))->whereIn('status',[5])->count();
        // $PresentPending = $attendaceToday+$NTA;
        // $absentTodayNew = round($userCount - $PresentPending);
        $areaCount   = Area::count();
        $totalArea     = Area::pluck('id')->toArray();
 

        $attendanceAreaCount = Attendance::whereIn('in_location_id',$totalArea)->where('date', Carbon::now()->format('Y-m-d'))->distinct('in_location_id')->count('in_location_id');
       
        $reaminingAreaCount  = round($areaCount - $attendanceAreaCount);

        $user = User::where('role',5)->pluck('area_id');

        $branchName   = Area::whereIn('id',$user)->whereNotIn('id',[865,866])->orderBy('address','asc')->get();
        $notLoggedInEmployee  = User::with('area')->whereNull('device_id')->whereNotIn('role',[1,2])->where('status',1)->orderBy('id','desc')->get();
        //return $notLoggedInEmployee;
       
        $loggedUser  = UserLog::whereBetween('login_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->pluck('user_id');
        $oneWeekEmployee = User::with('area')->whereNotIn('id',$loggedUser)->whereNotIn('role',[1,2])->where('status',1)->orderBy('id','desc')->get();
        //bar chart based on current month
        $month=$request->month;
        if(!empty($month)){
            $date = date($month);
        }else{
            $date = date('m');
        }

        $years=$request->year;
        
        if(!empty($years)){
            $year = date($years);
        }else{
            $year = date('Y');
        }

        $area    =Null;
        $areas   = Area::whereIn('id',$user)->whereNotIn('id',[865,866])->orderBy('address','asc')->first();
        $branch = $request->branch;
        if (!empty($branch)) {
            $area = $branch;
        } else {
            $area = $areas ? $areas->id : null;
        }
       
        $today = today(); 
        $dates = []; 

        // for($i=1; $i < $today->daysInMonth + 1; ++$i) {
        //     $dates[] = \Carbon\Carbon::createFromDate($today->year, $today->month, $i)->format('d');
        // }
        $result = Attendance::whereMonth('date',$date)->whereYear('date',$year)->where('in_location_id',$area)->select("date", "status",'in_location_id')->groupBy('date')->orderBy('date','asc')->get();
        
        return view('home', compact('userCount', 'attendaceToday', 'areaCount','absentTodayNew','branchName','notLoggedInEmployee','oneWeekEmployee','result','branch','month','years','area','deactiveUser','activeUser','activeUserCount','attendanceAreaCount','reaminingAreaCount'));
    }
    public function employeeNotLoggedOneWeek(Request $request) 
    {

       return Excel::download(new EmployeeNotLoggedOneWeek, 'report.xlsx');
    }

    public function employeeNotLoggedTillTdate(Request $request) 
    {
       return Excel::download(new EmployeeNotLoggedTillDate, 'report.xlsx');
    }

}
