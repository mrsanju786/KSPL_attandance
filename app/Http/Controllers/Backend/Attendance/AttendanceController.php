<?php

namespace App\Http\Controllers\Backend\Attendance;

use Auth;
use Excel;
use Config;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\LeaveLog;
use App\Models\Area;
use App\Models\State;
use App\Models\RsmTsm;
use App\Models\TsmRsm;
use App\Models\AssignRole;
use Carbon\CarbonPeriod;
use App\Models\AttendanceStatus;
use App\AttendanceExport;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AttendanceController extends Controller
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
     * More info DataTables : https://yajrabox.com/docs/laravel-datatables/master
     *
     * @param Datatables $datatables
     * @param Request $request
     * @return Application|Factory|Response|View
     * @throws \Exception
     */
    public function index(Datatables $datatables, Request $request)
    {
        $roles=Role::whereIn('id',[3,5,6,7,8])->get();
        $columns = [
            'employee_id' => ['name' => 'user.emp_id'],
            'name' => ['name' => 'user.name'],
            'image' => ['title' => 'Image'],
            'date',
            'in_time',
            'in_location_id' => ['name' => 'areaIn.address', 'title' => 'Branch Name'],
            'branch_id' => ['title' => 'Sole ID'],
            'role',
            'user_status' =>['title'=>'User Status'],
            'worker_device_id',
            
            
            // 'out_time',
            // 'work_hour',
            // 'over_time',
            // 'late_time',
            // 'early_out_time',
            
            // 'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location'],
            'status'=>['title'=>'Att. Status'],
            'status_updated_at',
            'status_updated_by',
            'approved_designation'=>['title'=>'Approved Designation'],
            
            'Reason'=>['title'=>'Att. Reason'],
            
        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);
        $month=$request->monthly;
        $role=$request->role;
        
        // DB::connection()->enableQueryLog();
        // $data = Attendance::with('user', 'areaIn', 'areaOut')
        // ->select('attendances.*')->get();
        // $query = DB::getQueryLog();
        // dd($query);
    
        if ($datatables->getRequest()->ajax()) {
            
            // $query = Attendance::with('user', 'areaIn', 'areaOut')
            //     ->select('attendances.*');
            $query = Attendance::with('user', 'areaIn', 'areaOut','attendanceStatus')->select('attendances.*')->whereHas('user', function ($q) {
                $q->where('status',1);
            });
            
            if ($from && $to) {
                $query = $query->whereBetween('date', [$from, $to]);
            }

            // worker
            // if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
            //     $query = $query->where('worker_id', Auth::user()->id);
            // }
            if($month){
                $date_arr = explode("-",$month);
                $query = $query->whereMonth('date', $date_arr[1])->whereYear('date', $date_arr[0]);
            }
            if($role==1){
                $query = $query;
            }else{
                $query = $query->where('worker_role_id',$role);
            }
            // if($role){
            //     $query = $query->where('worker_role_id',$role);
            // }
            
            $query = $query->orderBy('date','desc');
            return $datatables->of($query)
                ->addColumn('employee_id', function (Attendance $data) {
                    return $data->user->emp_id;
                })
                ->addColumn('name', function (Attendance $data) {
                    return $data->user->name;
                })
                ->addColumn('image', function (Attendance $data) {
                    $image = '<div style="display: flex;">';
                    // $data->message
                    
                  
                    if($data->image){
                        $getAssetFolder = asset('uploads/' . $data->image);
                        // title="'.$getAssetFolder.'"
                        // $image.='<div style="padding-right:3px;"><a data-toggle="tooltip"  ><img src="'.$getAssetFolder.'" width="50px" class="img-circle elevation-2"></a></div>';
                        // return '<img src="'.$getAssetFolder.'" width="50px" class="img-circle elevation-2">';
                        $image.='<div class="hoverable-image">
                        <img src="'.$getAssetFolder.'" width="50px" class="img-circle elevation-2">
                        <div><span style="background-color: gray; color: purple;"><img src="'.$getAssetFolder.'" class="img-circle elevation-2" style="width: 200px;"></span></div>
                        </div>';
                        return $image;
                    }else{
                        return "NA";
                    }
                    
                })
                ->addColumn('branch_id', function (Attendance $data) {
                    return $data->user->area_id==null?'':$data->user->area->name;
                })
                ->addColumn('role', function (Attendance $data) {
                    return (!empty(AssignRole::where('role_id', $data->user->userRole->id)->where(['company_id' => Auth::user()->company_id])->first()->display_name)) ? AssignRole::where('role_id', $data->user->userRole->id)->where(['company_id' => Auth::user()->company_id])->first()->display_name : Role::where('id', $data->user->userRole->id)->first()->display_name;
                })
                ->addColumn('in_location_id', function (Attendance $data) {
                    
                    return isset($data->areaIn->address) ? $data->areaIn->address : '';
                })
                // ->addColumn('out_location_id', function (Attendance $data) {
                //     return $data->out_location_id == null ? '' : $data->areaOut->address;
                // })
                ->addColumn('status', function (Attendance $data) {
                    if(User::where('id',$data->worker_id)->exists()){
                        $user = User::where('id',$data->worker_id)->whereIn('role',array(7,8))->first();
                        if($user){
                            return $data->status == 7 ? "Late" : "Present";
                        }else{
                            return isset($data->attendanceStatus->name)? $data->attendanceStatus->name : '';
                        }
                    }else{
                        return '-';
                    }
                })
                ->addColumn('status_updated_by', function (Attendance $data) {
                    return $data->status_updated_by == null ? '' : $data->attendanceUpdatedBy->name;
                })
                
                ->addColumn('Reason', function (Attendance $data) {
                    if($data->status ==3){
                        return $data->reason ?? "-";
                        
                    }elseif($data->additional_status == "Other"){
                        return $data->reason ?? "-";
                    }
                    elseif($data->additional_status != "Other"){
                        return $data->additional_status ?? "-";
                    }else{
                        return "-";
                    }
                })
                ->addColumn('user_status', function (Attendance $data) {
                    return $data->user->status==0?'Deactive':'Active';
                })

                ->addColumn('date', function (Attendance $data) {
                    return date('d-m-Y',strtotime($data->date));
                })

                ->addColumn('approved_designation', function (Attendance $data) {
                    $user = User::where('id',$data->status_updated_by)->first();
                    if(!empty($user)){
                        if($user->role==6){
                            return  $user->userRole->display_name ?? "-";
                        }else{
                            return  $user->userRole->display_name ?? "-";
                        }
                        
                    }else{
                        return "-";
                    }
                    

                  
                })
                // ->rawColumns(['name','role' ,'out_location_id', 'in_location_id','status','status_updated_by'])
                ->rawColumns(['employee_id','name','image','date','branch_id','role' ,'user_status','worker_device_id','in_time','in_location_id','status','status_updated_at','status_updated_by','approved_designation','Reason','user_status'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[1,'desc'], [2,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.attendances.index', compact('html' ,'roles'));
    }

    public function horizontal_attendances(Datatables $datatables, Request $request)
    {
        $roles=Role::whereIn('id',[3,5,6,7,8])->get();
        $columns = [
            'employee_id',
            'name' => ['name' => 'user.name'],
            'branch_id'=> ['title' => 'Sole ID'],
            'date',
            'role',
            'user_status'=>['title'=>'User Status'],
            'worker_device_id',
            
            'in_time',
            // 'out_time',
            'work_hour',
            // 'over_time',
            'late_time',
            // 'early_out_time',
            'in_location_id' => ['name' => 'areaIn.name', 'title' => 'In Location'],
            // 'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location'],
            'status'=>['title'=>'Att. Status'],
            'status_updated_at',
            'status_updated_by',
            
        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);
        $month=$request->monthly;
        $role=$request->role;
        
        // DB::connection()->enableQueryLog();
        // $data = Attendance::with('user', 'areaIn', 'areaOut')
        // ->select('attendances.*')->get();
        // $query = DB::getQueryLog();
        // dd($query);
    
        if ($datatables->getRequest()->ajax()) {
            
            // $query = Attendance::with('user', 'areaIn', 'areaOut')
            //     ->select('attendances.*');
            $query = Attendance::with('user', 'areaIn', 'areaOut','attendanceStatus')->select('attendances.*')->whereHas('user', function ($q) {
                $q->where('status',1);
            });

            if ($from && $to) {
                $query = $query->whereBetween('date', [$from, $to]);
            }

            // worker
            // if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
            //     $query = $query->where('worker_id', Auth::user()->id);
            // }
            if($month){
                $query = $query->whereBetween('date', [$start_month, $end_month]);
            }

            // if($role){
            //     $query = $query->where('worker_role_id',$role);
            // }
            if($role==1){
                $query = $query;
            }else{
                $query = $query->where('worker_role_id',$role);
            }

            return $datatables->of($query)
                ->addColumn('employee_id', function (Attendance $data) {
                    return $data->user->emp_id;
                })
                ->addColumn('name', function (Attendance $data) {
                    return $data->user->name;
                })
                ->addColumn('branch_id', function (Attendance $data) {
                    return $data->user->area_id==null?'':$data->user->area->name;
                })
                ->addColumn('role', function (Attendance $data) {
                    return $data->user->userRole->display_name;
                })
                ->addColumn('in_location_id', function (Attendance $data) {
                    return $data->in_location_id == null ? '' : $data->areaIn->address;
                })
                // ->addColumn('out_location_id', function (Attendance $data) {
                //     return $data->out_location_id == null ? '' : $data->areaOut->address;
                // })
                ->addColumn('status', function (Attendance $data) {
                    if($data){
                        
                        return isset($data->attendanceStatus->name)? $data->attendanceStatus->name : '';
                    }
                })
                ->addColumn('status_updated_by', function (Attendance $data) {
                    return $data->status_updated_by == null ? '' : $data->attendanceUpdatedBy->name;
                })
                ->addColumn('user_status', function (Attendance $data) {
                    return $data->user->status==0?'Deactive':'Active';
                })
                // ->rawColumns(['name','role' ,'out_location_id', 'in_location_id','status','status_updated_by'])
                ->rawColumns(['employee_id','name','branch_id','role' , 'User Status','in_location_id','Att. Status','status_updated_by'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9,10,11,12];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[1,'desc'], [2,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.attendances.h_report', compact('html' ,'roles'));
    }

    /**
     * Fungtion show button for export or print.
     *
     * @param $columnsArrExPr
     * @return array[]
     */
    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Monthly Attendance";
        return [
            [
                'pageLength'
            ],
            [
                'extend' => 'csvHtml5',
                'filename' => $fileName,
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'pdfHtml5',
                'filename' => $fileName,
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'excelHtml5',
                'filename' => $fileName,
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'print',
                'filename' => $fileName,
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
        ];
    }

    // public function excelExport(Request $request){
    //     $month=$request->monthly;
    //     $role=$request->role;
    //     // $month='2022-03';

    //     $start_month=Carbon::parse($month)->firstOfMonth()->toDateString();
    //     $end_month=Carbon::parse($month)->endOfMonth()->toDateString();

    //     if($role){
    //         $users=Attendance::whereBetween('date', [$start_month, $end_month])->where('worker_role_id', $role)->groupBy('worker_id')->get(); 
    //     }else{
    //         $users=Attendance::whereBetween('date', [$start_month, $end_month])->groupBy('worker_id')->get();
    //     }
        
    //     // dd($users);

    //     $period = CarbonPeriod::create($start_month, $end_month);

    //     $dates=[];
 
    //     foreach ($period as $date) {
    //         array_push($dates,$date->format('Y-m-d'));
    //     }

    //     // $attendances=[];
    //     $data=[];
    //     foreach ($users as $key => $user) {
    //         /*$attendances[$key]['user']=$user;
    //         foreach ($dates as  $date_key=>$date_value) {
    //             $attendances[$key]['attendance'][$date_key]=[];
    //             // $attendances[$key]['attendance'][$date_key]['user']=$user;
    //             // $attendances[$key]['attendance'][$date_key]['date']=$date_value;
    //             $attendance=Attendance::where('date', $date_value )->where('worker_id',$user)->first();
    //             if($attendance==null){
    //                 $attendances[$key]['attendance'][$date_key]['status']='Absent';
    //             }
    //             else{
    //                 $attendances[$key]['attendance'][$date_key]['status']=$attendance->attendanceStatus->name;
    //             }
    //         }*/
    //         $areas = Area::where('id',$user->user->area_id)->first();
    //         $areas_address = isset($areas->address)?$areas->address:'';
    //         $areas_name = isset($areas->name)?$areas->name:'';
    //         $payable_days="0";
    //         $i="0";
    //         $pa="0";
    //         $od="0";
    //         $l="0";
    //         $a="0";
    //         $wo="0";
    //         $pl="0";
    //         $h="0";
    //         $payable_days1 ="0";
    //         $payable_days2 ="0";
    //         $data[$key][]=$user->user->emp_id;
    //         $data[$key][]=$user->user->name;
    //         $data[$key][]=$user->user->status == 0 ? "Deactive" : "Active";
    //         $data[$key][]=$user->user->area_id==''?'':$areas_name;
    //         $data[$key][]=$user->user->device_id == null ? '' : $user->user->device_id;
    //         $data[$key][]=$user->user->area_id==''?'':$areas_address;
    //         foreach ($dates as  $date_key=>$date_value) {
    //            //leave attendance add PL in reports
    //             $leave = LeaveLog::where('user_id',$user->worker_id)->where('status',1)->where('from_date', $date_value)->first();
    //             //holidat attendance
    //             $userarea  = DB::table('users')->where('id',$user->worker_id)->where('status',1)->first();
    //             if(!empty($userarea)){
    //                 $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
    //             }
                
    //             $holiday =Null;
    //             if(!empty($areastate)){
    //                 $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
    //                 ->where('date',$date_value)     
    //                 ->first();
    //             }    
    //             $attendance = Attendance::where('date', $date_value)->where('worker_id',$user->worker_id)->first();
                
    //             if($attendance==null){
    //                 if(Carbon::parse($date_value)->dayOfWeek == Carbon::SUNDAY){
    //                     $wo = $wo+1;
    //                     $data[$key][]='WO';

    //                 }elseif($leave != null){
    //                     $pl = $pl+1;
    //                     $data[$key][]='PL';
    //                 }
    //                 elseif($holiday != null){
    //                     $h = $h+1;
    //                     $data[$key][]='H';
    //                 }
    //                 else{
    //                     $a = $a+1;
    //                     $data[$key][]='A';
    //                 }

    //                 // $j++;
    //             }
    //             else{
                    
    //                 // $data[$key][]=$attendance->attendanceStatus->name;
    //                 if($attendance->attendanceStatus->id=='1'){
    //                     $pa = $pa+1;
    //                     // $payable_days=$i+1 ;
    //                     $data[$key][]='P';
    //                 }
    //                 elseif($attendance->attendanceStatus->id=='2'){
    //                     $a = $a+1;
    //                     $data[$key][]='A';
    //                 }  
    //                 elseif($attendance->attendanceStatus->id=='3'){
    //                     $od = $od+1;
    //                     // $payable_days1=$payable_days+1 ;
    //                     $data[$key][]='OD';
    //                 } 
    //                 elseif($attendance->attendanceStatus->id=='4'){
    //                     $h = $h+1;
    //                     $data[$key][]='H';
    //                 } 
    //                 elseif($attendance->attendanceStatus->id=='5'){
    //                     $a = $a+1;
    //                     $data[$key][]='A';
    //                 }
    //                 elseif($attendance->attendanceStatus->id=='6'){
    //                     $wo = $wo+1;
    //                     $data[$key][]='WO';
    //                 }  
    //                 elseif($attendance->attendanceStatus->id=='7'){
    //                     $l = $l+1;
    //                     // $payable_days2=$payable_days1+1 ;
                       
    //                     $data[$key][]='LT';
    //                 }
    //                 elseif($attendance->attendanceStatus->id=='8'){
    //                     $pl = $pl+1;
                       
    //                     $data[$key][]='PL';
    //                 }
    //                 elseif($attendance->attendanceStatus->id=='9'){
    //                     // $pl = $pl+1;
                       
    //                     $data[$key][]=$attendance->additional_status ?? "-";
    //                 }
    //                 $i++;
                   
    //             }
    //         }
            
    //         //count pa, lt, and od payable days
    //         $payable_days += ($pa + $l + $od + $h + $wo);
    //         $data[$key][]=!empty($payable_days) ? $payable_days:"0";
    //         $data[$key][]=!empty($pa)      ? $pa :"0";
    //         $data[$key][]=!empty($l)       ? $l  :"0";
    //         $data[$key][]=!empty($od)      ? $od :"0";
    //         $data[$key][]=!empty($wo)      ? $wo :"0";
    //         $data[$key][]=!empty($pl)      ? $pl :"0";
    //         $data[$key][]=!empty($h)       ? $h  :"0";        
    //         $data[$key][]=!empty($a)       ? $a  :"0";
              
           
    //     }       
       
    // //    dd(array_replace_recursive($static_responses,$dynamic_response));

    //     return Excel::download(new AttendanceExport($start_month,$end_month ,$data), 'Horizontal Monthly Attendance.xlsx');
    // }

    public function excelExport(Request $request){
        $month=$request->monthly;
        $role=$request->role;
        
        $start_month=Carbon::parse($month)->firstOfMonth()->toDateString();
        $end_month=Carbon::parse($month)->endOfMonth()->toDateString();
        // $user = User::where('role',$role)->pluck('id')->toArray();
        if($role == 1){

            $user = User::whereIn('role',[3,5,6,7,8])->pluck('id')->toArray();
            
        }else{
            $user = User::where('role',$role)->pluck('id')->toArray();
     
        }
        $attendance =Attendance::whereBetween('date', [$start_month, $end_month])->whereIn('worker_id', $user)->pluck('worker_id')->toArray(); 

        $userDiff = array_diff($user, $attendance);

        $allUser = array_merge($attendance, $userDiff);
        
        //get all users
        // $users = User::where('role',$role)->whereIn('id',$allUser)->get();
        $activeUsers = User::whereIn('id',$allUser)
                            ->where('status',1)
                            ->pluck('id')
                            ->toArray();
                            
        $deactiveusers = User::whereIn('id',$allUser)
                            ->where('status',0)
                            ->whereBetween('deactivated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->get()
                            ->pluck('id')
                            ->toArray();
       
        $totalUser  =  array_merge($activeUsers,$deactiveusers); 
       
        $users      =   User::whereIn('id',$totalUser)->get();   
        
        $period = CarbonPeriod::create($start_month, $end_month);

        $dates=[];
 
        foreach ($period as $date) {
            array_push($dates,$date->format('Y-m-d'));
        }

        $data=[];
        foreach ($users as $key => $user) {
         
            $areas = Area::where('id',$user->area_id)->first();
            $areas_address = isset($areas->address)?$areas->address:'';
            $areas_name = isset($areas->name)?$areas->name:'';
            $rsm_name  =Null;
            $rsm_name1 =Null;
            //tsm rsm name
            $emp  = DB::table('rsm_tsms')->where('tsm_id',$user->id)->pluck('rsm_id')->toArray();
            $tsm  = DB::table('tsm_emps')->where('emp_id',$user->id)->pluck('tsm_id')->toArray();
            
            if(!empty($emp)){
                $rsm_name = DB::table('users')->whereIn('id',$emp)->where('role',6)->where('status',1)->first();
            
            }
            if(!empty($tsm)){
                $rsm_name1 = DB::table('users')->whereIn('id',$tsm)->where('role',6)->where('status',1)->first();

                $tsmEmp = RsmTsm::where('tsm_id',$tsm)->pluck('rsm_id')->toArray();
                if(!empty($tsmEmp)){
                    $rsm_name1 = DB::table('users')->whereIn('id',$tsmEmp)->where('role',6)->where('status',1)->first();
                }
            }
            
         
            $payable_days="0";
            $i="0";
            $pa="0";
            $od="0";
            $l="0";
            $a="0";
            $wo="0";
            $pl="0";
            $h="0";
            $payable_days1 ="0";
            $payable_days2 ="0";
            $data[$key][]=$user->emp_id;
            $data[$key][]=$user->name;
            if($user->role ==3){
                $data[$key][]=$rsm_name1->name ?? "-";
            }elseif($user->role ==5){
                $data[$key][]=$rsm_name->name ?? "-";
            }else{
                $data[$key][]="-";
            } 
            $data[$key][]=$user->status == 0 ? "Deactive" : "Active";
            $data[$key][]=$areas_name ?? '-';
            $data[$key][]=$user->device_id == null ? '' : $user->device_id;
            $data[$key][]=$areas_address ?? '-';
            foreach ($dates as  $date_key=>$date_value) {
               //leave attendance add PL in reports
                $leave = LeaveLog::where('user_id',$user->id)->where('status',1)->where('from_date', $date_value)->first();
                //holidat attendance
                // $userarea  = DB::table('users')->where('id',$user->id)->where('status',1)->first();
                // if(!empty($userarea)){
                //     $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
                // }
                $areastate = DB::table('users')
                              ->leftjoin('areas','areas.id' ,'=','users.area_id')
                              ->select('areas.state')
                              ->where('users.id',$user->id)
                              ->first();

                $holiday =Null;
                if(!empty($areastate)){
                    $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
                    ->where('date',$date_value)     
                    ->first();
                }    
                $attendance = Attendance::where('date', $date_value)->where('worker_id',$user->id)->first();
                
                if($attendance==null){
                    if(Carbon::parse($date_value)->dayOfWeek == Carbon::SUNDAY){
                        $wo = $wo+1;
                        $data[$key][]='WO';

                    }elseif($leave != null){
                        $pl = $pl+1;
                        $data[$key][]='PL';
                    }
                    elseif($holiday != null){
                        $h = $h+1;
                        $data[$key][]='H';
                    }
                    else{
                        $a = $a+1;
                        $data[$key][]='A';
                    }

                    // $j++;
                }
                else{
                    
                    // $data[$key][]=$attendance->attendanceStatus->name;
                    if($attendance->attendanceStatus->id=='1'){
                        $pa = $pa+1;
                        // $payable_days=$i+1 ;
                        $data[$key][]='P';
                    }
                    elseif($attendance->attendanceStatus->id=='2'){
                        $a = $a+1;
                        $data[$key][]='A';
                    }  
                    elseif($attendance->attendanceStatus->id=='3'){
                        $od = $od+1;
                        // $payable_days1=$payable_days+1 ;
                        $data[$key][]='OD';
                    } 
                    elseif($attendance->attendanceStatus->id=='4'){
                        $h = $h+1;
                        $data[$key][]='H';
                    } 
                    elseif($attendance->attendanceStatus->id=='5'){
                        $a = $a+1;
                        $data[$key][]='A';
                    }
                    elseif($attendance->attendanceStatus->id=='6'){
                        $wo = $wo+1;
                        $data[$key][]='WO';
                    }  
                    elseif($attendance->attendanceStatus->id=='7'){
                        $l = $l+1;
                        // $payable_days2=$payable_days1+1 ;
                       
                        $data[$key][]='LT';
                    }
                    elseif($attendance->attendanceStatus->id=='8'){
                        $pl = $pl+1;
                       
                        $data[$key][]='PL';
                    }
                    $i++;
                   
                }
            }
            
            //count pa, lt, and od payable days
            $payable_days += ($pa + $l + $od + $h + $wo);
            $data[$key][]=!empty($payable_days) ? $payable_days:"0";
            $data[$key][]=!empty($pa)      ? $pa :"0";
            $data[$key][]=!empty($l)       ? $l  :"0";
            $data[$key][]=!empty($od)      ? $od :"0";
            $data[$key][]=!empty($wo)      ? $wo :"0";
            $data[$key][]=!empty($pl)      ? $pl :"0";
            $data[$key][]=!empty($h)       ? $h  :"0";        
            $data[$key][]=!empty($a)       ? $a  :"0";
              
           
        }       
       
        return Excel::download(new AttendanceExport($start_month,$end_month ,$data), 'Horizontal Monthly Attendance.xlsx');
    }

    /**
     * Get script for the date range.
     *
     * @return string
     */
    public function scriptMinifiedJs()
    {
        // Script to minified the ajax
        return <<<CDATA
            var formData = $("#date_filter").find("input").serializeArray();
            $.each(formData, function(i, obj){
                data[obj.name] = obj.value;
            });

            var formData_role = $("#role_filter").find("select").serializeArray();
            $.each(formData_role, function(i, obj){
                data[obj.name] = obj.value;
            });
CDATA;
    }

    public function attendanceView(Datatables $datatables ,Request $request){
        // $query = DB::table('attendances')
        // ->join('users', 'attendances.worker_id', '=', 'users.id')
        // ->join('areas','users.area_id', '=', 'areas.id')
        // ->select('attendances.*', 'users.name as user_name', 'users.emp_id as emp_id','areas.address','areas.id as area_id')->where('date','>=',$request->dateFrom)->where('date','<=',$request->dateTo)->whereIn('attendances.worker_role_id', ['3','5','6'])->orderBy('date','ASC')->get();
        // return $query;die();
        $tsm=User::get();
        $rsm=User::get();
        $employees=User::all();
        // $roles = Role::whereIn('id',array(3,7,8))->get();
        $roles = Role::whereIn('id',array(3,7,8))->get();
        $states = State::all();
        $holidaysStatus = AttendanceStatus::whereNotIn('id',[2])->get();
        //dd($states);
        $columns = [
            'employee_id'  => ['title'=>'Employee ID'],
            'name' => ['title'=>'Employee Name'],
            'image' => ['title' => 'Image'],
            'date',
            'in_time',
            'out_time',
            //'in_location_id' => ['name' => 'areaIn.address', 'title' => 'Branch Name'],
            //'branch_id' => ['title' => 'Sole ID'],
            'role',
            'user_status' =>['title'=>'User Status'],
            // 'work_hour',
            // 'over_time',
            // 'late_time',
            // 'early_out_time',
            // 'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location'],
            'status'=>['title'=>'Att. Status'],
            'status_updated_at',
            'status_updated_by',
            'action' => ['orderable' => false, 'searchable' => false]

        ];

        $from = $request->dateFrom ? date($request->dateFrom) : null;
        $to = $request->dateTo ? date($request->dateTo) : null;
        $stateId = $request->state_id;
        $areaId = $request->area_id;
        $holidayDate = $request->holiday_date;

        $month=$request->monthly;
       
        $year=$request->yearly;
        
        // $month='2022-03';

        $start_month = $month ? Carbon::parse($month)->firstOfMonth()->toDateString() : null;
        $end_month = $month ? Carbon::parse($month)->endOfMonth()->toDateString() : null;
        
        $start_year = $year ? Carbon::create($year)->startOfYear()->toDateString() : null;
        $end_year = $year ? Carbon::create($year)->endOfYear()->toDateString() : null;

        // $tsm_value=$request->tsm;
        $rsm_value=$request->rsm;
        $employee=$request->employee;
        $role=$request->role;

        $tsm_value='666';
        // $user=TsmEmp::where('tsm_id',$tsm_value)->first();
        // dd($user);


        if ($datatables->getRequest()->ajax()) {

                // Default to current day if no date range is provided
            $today = Carbon::today()->toDateString();
            $defaultFrom = $today;
            $defaultTo = $today;

            // Adjust default range if specific range is provided
            if ($from && $to) {
                $defaultFrom = $from;
                $defaultTo = $to;
            } elseif ($month) {
                $defaultFrom = $start_month;
                $defaultTo = $end_month;
            } elseif ($year) {
                $defaultFrom = $start_year;
                $defaultTo = $end_year;
            }

            // Get all active employees in the current company and branch
            $allEmployees = User::join('areas', 'areas.id', '=', 'users.area_id')
                ->where(['users.status' => 1])
                ->select('users.*', 'users.area_id', 'areas.state', 'areas.id as areaid')
                ->whereNotIn('role', [1, 10])
                ->get();

            // Fetch all attendance records within the date range
            $attendances = Attendance::whereHas('user', function ($query) {
                $query->where(['status' => 1]);
            })
            ->whereBetween('date', [$defaultFrom, $defaultTo])
            ->groupBy('date')
            ->get();

            // Store attendance results
            $attendanceResults = [];

            foreach ($allEmployees as $employee) {
              $currentDate = Carbon::parse($defaultFrom);

                while ($currentDate->lte($defaultTo)) {
                    $date = $currentDate->toDateString();
                    $attendance = Attendance::firstWhere(['worker_id' => $employee->id, 'date' => $date]);

                    if ($attendance) {
                        $attendanceResults[] = [
                            'id' => $attendance->id,
                            'date' => $date,
                            'worker_id' => $employee->id,
                            'state' => $employee->state,
                            'area_id' => $employee->area_id,
                            'worker_role_id' => $employee->role,
                            'status' => $attendance->status,
                            'status_updated_at' => $attendance->status_updated_at,
                            'status_updated_by' => $attendance->status_updated_by,
                            'in_time' => $attendance->in_time,
                            'out_time' => $attendance->out_time,
                        ];
                    } else {
                        $attendanceResults[] = [
                            'id' => 0,
                            'date' => $date,
                            'worker_id' => $employee->id,
                            'state' => $employee->state,
                            'area_id' => $employee->area_id,
                            'worker_role_id' => $employee->role,
                            'status' => 5, // Absent
                            'status_updated_at' => null,
                            'status_updated_by' => null,
                            'in_time' => null,
                            'out_time' => null,
                        ];
                    }

                    $currentDate->addDay();
                }
            }

            $attendanceResults = collect(array_values($attendanceResults));

            $query = $attendanceResults;

            if ($stateId) {
                $query = $query->where('state', $stateId);
            }

            if ($areaId) {
                $area_id = explode(",", $areaId);
                $query = $query->whereIn('area_id', $area_id);
            }

            if ($holidayDate) {
                $query = $query->where('date', $holidayDate);
            }

            if ($role != 1) {
                $query = $query->where('worker_role_id', $role);
            }

            $query = $query->sortByDesc('date');
            // echo "<pre>";
            // print_r($query->toArray()); // Convert to array for printing
            // echo "</pre>";
            // exit();
            //dd($query);
            return $datatables->of($query) 
                ->addColumn('employee_id', function ($data) {
                    $user = User::find($data['worker_id']); // Retrieve user based on worker_id
                    return $user ? $user->emp_id : '-'; // Return emp_id or '-' if null
                })
                ->addColumn('name', function ($data) {
                    $user = User::find($data['worker_id']); // Retrieve user based on worker_id
                    return $user ? $user->name : '-'; // Return name or '-' if null
                })
                ->addColumn('image', function ($data) {

                    $attendance = Attendance::where(['date'=>$data['date'],'worker_id'=>$data['worker_id']])->first();
                    if(!empty($attendance)){
                        $image = '<div style="display: flex;">';
                        // $data->message
                        
                    
                        if($attendance->image){
                            $getAssetFolder = asset('uploads/' . $attendance->image);
                            // title="'.$getAssetFolder.'"
                            // $image.='<div style="padding-right:3px;"><a data-toggle="tooltip"  ><img src="'.$getAssetFolder.'" width="50px" class="img-circle elevation-2"></a></div>';
                            // return '<img src="'.$getAssetFolder.'" width="50px" class="img-circle elevation-2">';

                            //<img src="'.$getAssetFolder.'" class="img-circle elevation-2" style="width: 200px;"></span></div>
                            $image.='<div class="">
                            <img src="'.$getAssetFolder.'" width="50px" class="img-circle elevation-2">
                            <div><span style="background-color: gray; color: purple;">
                            </div>';
                            return $image;
                        }else{
                            return "NA";
                        }
                    }else{
                        return "NA";
                    }
                    
                })
                // ->addColumn('branch_id', function (Attendance $data) {
                //     // return $data->user->area_id==null?'':$data->user->area->id;
                //     if(!isset($data->user->area_id)){return " ";}else{ if(isset($data->user->area->name)){return $data->user->area->name;} }
                // })
                ->addColumn('role', function ($data) {
                    return (!empty(AssignRole::where('role_id', $data['worker_role_id'])->where(['company_id' => Auth::user()->company_id])->first()->display_name)) ? AssignRole::where('role_id', $data['worker_role_id'])->where(['company_id' => Auth::user()->company_id])->first()->display_name : Role::where('id', $data['worker_role_id'])->first()->display_name;
                })
                ->addColumn('status', function ($data) {
                    if(User::where(['id'=>$data['worker_id']])->exists()){
                        $user = User::where(['id'=>$data['worker_id']])->whereIn('role',array(7,8))->first();
                        if($user){
                            return $data['status'] == 7 ? "Late" : '<p class="status present">Present</p>';
                        }else{
                            if ($data['status'] == 1) {
                                return '<p class="status present">Present</p>';
                            } elseif ($data['status'] == 2 || $data['status'] == 5) {
                                return '<p class="status absent">Absent</p>';
                            } elseif ($data['status'] == 3) {
                                return '<p class="status out-door">Out Door</p>';
                            } elseif ($data['status'] == 4) {
                                return '<p class="status holiday">Holiday</p>';
                            } elseif ($data['status'] == 6) {
                                return '<p class="status holiday">Week Off</p>';
                            } elseif ($data['status'] == 8) {
                                return '<p class="status holiday">Paid Leave</p>';
                            }elseif ($data['status'] == 10) {
                                return '<p class="status early-leave">Half Day</p>';
                            }elseif ($data['status'] == 11) {
                                return '<p class="status early-leave">Early Leave</p>';
                            }
                        }
                    }else{
                        return '-';
                    }
                    
                })
                ->addColumn('status_updated_at', function ($data) { 
                    return $data['status_updated_at'] ?? "-";
                })
                ->addColumn('status_updated_by', function ($data) { 
                    $user = User::find($data['status_updated_by']);
                    return $user->name ?? "-";
                })
                
                ->addColumn('user_status', function ($data) {
                    $user = User::whereNotIn('role',[1,10])->find($data['worker_id']);
                    //dd($user);
                    return $user->status==0 ?'Deactive':'Active';
                })

                ->addColumn('date', function ($data) {
                    return date('d-m-Y',strtotime($data['date']));
                })
                ->addColumn('action', function ($data) {
     
                   $attendanceStatus = AttendanceStatus::whereNotIn('id',[2])->select('id','name')->get()->toArray();
                   $encodedAttendanceStatus = htmlspecialchars(json_encode($attendanceStatus));

                    $button = '<div class="col-sm-12"><div class="row">';
                    if (Auth::user()->hasRole('administrator')) { // Check the role
                        $button .= '<div class="col-sm-6"><button class="btn btn-primary" data-toggle="modal" data-target="#editModal" data-id="'.$data['id'].'"
                        data-status="'.$data['status'].'"
                        data-date="'.$data['date'].'"
                        data-worker_id="'.$data['worker_id'].'"
                        data-worker_role_id="'.$data['worker_role_id'].'"
                        data-check_in="'.$data['in_time'].'"
                        data-check_out="'.$data['out_time'].'"
                        data-attendance_status="'.$encodedAttendanceStatus.'"><i class="fa fa-edit"></i></button></div>';
                    } else {
                        $button .= '<div class="col-sm-6"><button class="btn btn-primary disabled"><i class="fa fa-edit"></i></button></div>';
                    }
                    $button .= '</div></div>';
                
                    return $button;
                })
                
                // ->addColumn('action', function (Attendance $data) {
                //     //$routeEdit = route($this->getRoute() . ".edit", $data->id);
                
                //     $button = '<div class="col-sm-12"><div class="row">';
                //     if (Auth::user()->hasRole('administrator')) { // Correct the spelling of 'administrator'
                //         // Add 'edit-button' class and data attributes to trigger the modal
                //         $button = '<div class="col-sm-6"><button class="btn btn-primary edit-button" 
                //                    data-attendance-id="'.$data->id.'"
                //                    data-status="'.$data->status.'"
                //                    data-check-in="'.$data->check_in.'"
                //                    data-check-out="'.$data->check_out.'">
                //                    <i class="fa fa-edit"></i>
                //                    </button></div> ';
                //     } else {
                //         $button = '<div class="col-sm-6"><button class="btn btn-primary disabled"><i class="fa fa-edit"></i></button></div> ';
                //     }
                //     $button .= '</div></div>';
                //     return $button;
                // })
                // ->rawColumns(['name', 'out_location_id', 'in_location_id','status','status_updated_by'])
                ->rawColumns(['employee_id','name','image','date','branch_id','role','states' ,'User Status','worker_device_id','in_time','out_time','in_location_id','Att .status','status_updated_at','status_updated_by','status','action'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9,10,11];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[4,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->AttendancebuttonDatatables($columnsArrExPr),
            ]);
    return view('backend.attendances.attendance_manage', compact('html','states','holidaysStatus'));
}

public function AttendancebuttonDatatables($columnsArrExPr)
{
    $fileName = "Attendance Management";
    return [
        [
            'pageLength'
        ],
        [
            'extend' => 'csvHtml5',
            'filename' => $fileName,
            'exportOptions' => [
                'columns' => $columnsArrExPr
            ]
        ],
        [
            'extend' => 'pdfHtml5',
            'filename' => $fileName,
            'exportOptions' => [
                'columns' => $columnsArrExPr
            ]
        ],
        [
            'extend' => 'excelHtml5',
            'filename' => $fileName,
            'exportOptions' => [
                'columns' => $columnsArrExPr
            ]
        ],
        [
            'extend' => 'print',
            'filename' => $fileName,
            'exportOptions' => [
                'columns' => $columnsArrExPr
            ]
        ],
    ];
}

    public function attendanceUpdate(Request $request, $attendanceId){

        $attendanceUpdate = Attendance::find($attendanceId);
    if($attendanceUpdate){
            $attendanceUpdate->status = $request->status;
            $attendanceUpdate->in_time = $request->check_in;
            $attendanceUpdate->out_time = $request->check_out;
            $attendanceUpdate->status_updated_at = Carbon::now();
            $attendanceUpdate->status_updated_by = Auth::user()->id;
            $attendanceUpdate->save();
    }else{ 
            $attendanceMarked = new Attendance();
            $attendanceMarked->date = $request->date;
            $attendanceMarked->worker_id = $request->worker_id;
            $attendanceMarked->worker_role_id = $request->worker_role_id;
            $attendanceMarked->status = $request->status;
            $attendanceMarked->in_time = $request->check_in;
            $attendanceMarked->out_time = $request->check_out;
            $attendanceMarked->status_updated_at = Carbon::now();
            $attendanceMarked->status_updated_by = Auth::user()->id;
            $attendanceMarked->save();

    }

        return redirect()->route('attendance-management')->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
    }
}
