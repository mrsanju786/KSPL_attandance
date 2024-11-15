<?php

namespace App\Http\Controllers\Backend\Reports;

use Auth;
use Config;
use Session;
use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Designation;
use App\Models\Role;
use App\Models\Leave;
use App\Models\AssignRole;
use App\Models\TsmEmp;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use App\Exports\UsersExport;
use App\Exports\OBSTExport;
use App\Exports\DSTExport;
use App\Exports\boaExport;
use App\Exports\TSMReportExport;
use App\Exports\RSMReportExport;
use App\Exports\AllReportExport;
use Maatwebsite\Excel\Facades\Excel;
class ReportsController extends Controller
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
    public function export(Request $request) 
    {
        Session::put('date1', $request->date1);
        Session::put('date2', $request->date2);

       return Excel::download(new UsersExport, 'Attendance.xlsx');
    }
    public function exportOBST(Request $request) 
    {
        Session::put('date1', $request->date1);
        Session::put('date2', $request->date2);
        
       return Excel::download(new OBSTExport, 'Horizontal Attendance.xlsx');
    }
    public function exportDST(Request $request) 
    {
        Session::put('date1', $request->date1);
        Session::put('date2', $request->date2);

       return Excel::download(new DSTExport, 'Horizontal Attendance.xlsx');
    }
    public function exportBOA(Request $request) 
    {
        Session::put('date1', $request->date1);
        Session::put('date2', $request->date2);

       return Excel::download(new boaExport, 'Horizontal Attendance.xlsx');
    }
    public function exportTSM(Request $request) 
    {
        Session::put('date1', $request->date1);
        Session::put('date2', $request->date2);

       return Excel::download(new TSMReportExport, 'Horizontal Attendance.xlsx');
    }
    public function exportRSM(Request $request) 
    {
        Session::put('date1', $request->date1);
        Session::put('date2', $request->date2);

       return Excel::download(new RSMReportExport, 'Horizontal Attendance.xlsx');
    }
    public function exportAll(Request $request) 
    {
        Session::put('date1', $request->date1);
        Session::put('date2', $request->date2);

       return Excel::download(new AllReportExport, 'All Attendance.xlsx');
    }
    // horizontal_report start
    public function horizontal_report(Datatables $datatables, Request $request){
        $tsm=User::where('role',5)->get();
        $rsm=User::where('role',6)->get();
        $employees=User::all();
        $roles = Role::whereIn('id',array(3,7,8))->get();
        $columns = [
            'employee_id',
            'name' => ['name' => 'user.name'],
            'date',
            'branch_id' => ['title' => 'Sole ID'],
            'role',
            'user_status'=>['title'=>'User Status'],
            'worker_device_id',
            
            'in_time',
            // 'out_time',
            // 'work_hour',
            // 'over_time',
            // 'late_time',
            // 'early_out_time',
            'in_location_id' => ['name' => 'areaIn.name', 'title' => 'Branch Name'],
            // 'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location'],
            'status'=>['title'=>'Att. Status'],
            'status_updated_at',
            'status_updated_by',

        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);

        $month=$request->monthly;
        $year=$request->yearly;
        // $month='2022-03';

        $start_month=Carbon::parse($month)->firstOfMonth()->toDateString();
        $end_month=Carbon::parse($month)->endOfMonth()->toDateString();

        $start_year=Carbon::create($year)->startOfYear()->toDateString();
        $end_year=Carbon::create($year)->endOfYear()->toDateString();

        // $tsm_value=$request->tsm;
        $rsm_value=$request->rsm;
        $employee=$request->employee;
        $role=$request->role;

        $tsm_value='666';
        // $user=TsmEmp::where('tsm_id',$tsm_value)->first();
        // dd($user);


        if ($datatables->getRequest()->ajax()) {
            // $query = Attendance::with('user', 'areaIn', 'areaOut','attendanceStatus')
            //     ->select('attendances.*');
        
            $query = Attendance::with('user', 'areaIn', 'areaOut','attendanceStatus')->select('attendances.*')->whereHas('user', function ($q) {
                $q->where('status',1);
            });
            
            if ($from && $to) {
                $query = $query->whereBetween('date', [$from, $to]);
            }

            if($month){
                $query = $query->whereBetween('date', [$start_month, $end_month]);
            }

            if($year){
                $query = $query->whereBetween('date', [$start_year, $end_year]);
            }

            if($employee){
                $query = $query->where('worker_id', $employee); 
            }
            // if($role){
            //     $query = $query->where('worker_role_id', $role); 
            // }

            if($role==1){
                $query = $query; 
            }else{
                $query = $query->where('worker_role_id', $role); 
            }
            // if($tsm_value){
            //     $query=$query->where('worker_id',(TsmEmp::where('tsm_id',$tsm_value))->get());
            // }

            // worker
            // if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
            //     $query = $query->where('worker_id', Auth::user()->id);
            // }

            return $datatables->of($query) 
                ->addColumn('employee_id', function (Attendance $data) {
                    return $data->user->emp_id;
                })
                ->addColumn('name', function (Attendance $data) {
                    return $data->user->name;
                })
                ->addColumn('branch_id', function (Attendance $data) {
                    // return $data->user->area_id==null?'':$data->user->area->id;
                    if(!isset($data->user->area_id)){return " ";}else{ if(isset($data->user->area->name)){return $data->user->area->name;} }
                })
                ->addColumn('role', function (Attendance $data) {
                    return isset($data->user->userRole->display_name)? $data->user->userRole->display_name : '';
                })
                ->addColumn('in_location_id', function (Attendance $data) {
                    // return $data->in_location_id == null ? '' : $data->areaIn->address;
                    if(!isset($data->in_location_id)){return " ";}else{ if(isset($data->areaIn->address)){return $data->areaIn->address;} }
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
                // ->rawColumns(['name', 'out_location_id', 'in_location_id','status','status_updated_by'])
                ->rawColumns(['employee_id','name','branch_id','role' ,'user_status','in_location_id','status','status_updated_by'])
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

        return view('backend.reports.h_report', compact('html' ,'tsm','rsm','employees','roles'));
    }
    // horizontal report end

    public function index(Datatables $datatables, Request $request)
    {
        // $query = DB::table('attendances')
        // ->join('users', 'attendances.worker_id', '=', 'users.id')
        // ->join('areas','users.area_id', '=', 'areas.id')
        // ->select('attendances.*', 'users.name as user_name', 'users.emp_id as emp_id','areas.address','areas.id as area_id')->where('date','>=',$request->dateFrom)->where('date','<=',$request->dateTo)->whereIn('attendances.worker_role_id', ['3','5','6'])->orderBy('date','ASC')->get();
        // return $query;die();
        $tsm=User::where('role',5)->get();
        $rsm=User::where('role',6)->get();
        $employees=User::all();
        // $roles = Role::whereIn('id',array(3,7,8))->get();
        $roles = Role::whereIn('id',array(3,7,8))->get();
        $columns = [
            'employee_id'  => ['name' => 'user.emp_id'],
            'name' => ['title'=>'Name'],
            'designation' => ['title' => 'Designation'],
            'image' => ['title' => 'Image'],
            'date',
            'in_time',
            'out_time',
            'work_hours' => ['title' => 'Work Hours'],
            'branch_id' => ['title' => 'Sole ID'],
            'role',
            'user_status' =>['title'=>'User Status'],
            // 'over_time',
            // 'late_time',
            // 'early_out_time',
           
            // 'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location'],
            'status'=>['title'=>'Att. Status'],
            'status_updated_at',
            'status_updated_by',
            'approved_designation'=>['title'=>'Approved Designation'],
            

        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);

        $month=$request->monthly;
        $year=$request->yearly;
        // $month='2022-03';

        $start_month=Carbon::parse($month)->firstOfMonth()->toDateString();
        $end_month=Carbon::parse($month)->endOfMonth()->toDateString();

        $start_year=Carbon::create($year)->startOfYear()->toDateString();
        $end_year=Carbon::create($year)->endOfYear()->toDateString();

        // $tsm_value=$request->tsm;
        $rsm_value=$request->rsm;
        $employee=$request->employee;
        $role=$request->role;

        $tsm_value='666';
        // $user=TsmEmp::where('tsm_id',$tsm_value)->first();
        // dd($user);


        if ($datatables->getRequest()->ajax()) {
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
            // Ensure $attendanceResults contains instances of Attendance
            $attendanceResults = $attendanceResults->map(function ($item) {
                return new Attendance($item);
            });

            $query = $attendanceResults->sortByDesc('date');
            
            return $datatables->of($query) 
                ->addColumn('employee_id', function (Attendance $data) {
                    return $data->user->emp_id ?? "-";
                })
                ->addColumn('name', function (Attendance $data) {
                    return $data->user->name ?? "-";
                })
                ->addColumn('designation', function (Attendance $data) {
                    $designation = Designation::find($data->user->designation);
                    return !empty($designation) ? $designation->name : "NA";
                })
                ->addColumn('image', function (Attendance $data) {

                    $attendance = Attendance::where(['date'=>$data->date,'worker_id'=>$data->worker_id])->first();
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
                ->addColumn('branch_id', function (Attendance $data) {
                    // return $data->user->area_id==null?'':$data->user->area->id;
                    if(!isset($data->user->area_id)){return " ";}else{ if(isset($data->user->area->name)){return $data->user->area->name;} }
                })
                ->addColumn('role', function (Attendance $data) {
                    return (!empty(AssignRole::where('role_id', $data->user->userRole->id)->where(['company_id' => Auth::user()->company_id])->first()->display_name)) ? AssignRole::where('role_id', $data->user->userRole->id)->where(['company_id' => Auth::user()->company_id])->first()->display_name : Role::where('id', $data->user->userRole->id)->first()->display_name;
                    //return isset($data->user->userRole->display_name)? $data->user->userRole->display_name : '';
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

                            $leave = Leave::where('user_id', $data->worker_id)
                            ->where('from_date', '<=', $data->date)
                            ->where('to_date', '>=', $data->date)
                            ->where('status',1)
                            ->first();
  
                            if($leave) {
                                
                                if ($leave->leave_type == "PL") {
                                    return '<p class="status early-leave">Paid Leave⛱️</p>';
                                }elseif($leave->leave_type == "SL"){
                                    return '<p class="status holiday">Sick Leave🤒</p>';
                                }else{
                                    return '<p class="status holiday">Casual Leave🤷‍♂️</p>';
                                } 
                            }

                            if ($data->status == 1) {
                                return '<p class="status present">Present</p>';
                            } elseif ($data->status == 2 || $data->status == 5) {
                                return '<p class="status absent">Absent</p>';
                            } elseif ($data->status == 3) {
                                return '<p class="status out-door">Out Door</p>';
                            } elseif ($data->status == 4) {
                                return '<p class="status holiday">Holiday</p>';
                            } elseif ($data->status == 6) {
                                return '<p class="status holiday">Week Off</p>';
                            } elseif ($data->status == 8) {
                                return '<p class="status holiday">Paid Leave</p>';
                            }elseif ($data->status == 10) {
                                return '<p class="status early-leave">Half Day</p>';
                            }elseif ($data->status == 11) {
                                return '<p class="status early-leave">Early Leave</p>';
                            }
                        }
                    }else{
                        return '-';
                    }
                    
                })
                ->addColumn('status_updated_at', function (Attendance $data) { 
                    return $data->status_updated_at ?? "-";
                })
                ->addColumn('status_updated_by', function (Attendance $data) {
                    return $data->attendanceUpdatedBy->name ?? "-";
                })
                
                // ->addColumn('Reason', function (Attendance $data) {
                //     if($data->status ==3){
                //         return $data->reason ?? "-";
                        
                //     }elseif($data->additional_status == "Other"){
                //         return $data->reason ?? "-";
                //     }
                //     elseif($data->additional_status != "Other"){
                //         return $data->additional_status ?? "-";
                //     }else{
                //         return "-";
                //     }
                // })
                ->addColumn('user_status', function (Attendance $data) {
                    return $data->user->status==0?'Deactive':'Active';
                })

                ->addColumn('date', function (Attendance $data) {
                    return date('d-m-Y',strtotime($data->date));
                })
                ->addColumn('work_hours', function (Attendance $data) {
                    $attendance = Attendance::where(['date'=>$data->date,'worker_id'=>$data->worker_id])->first();
                    return $attendance->work_hour ?? "-";
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
                // ->rawColumns(['name', 'out_location_id', 'in_location_id','status','status_updated_by'])
                ->rawColumns(['employee_id','name','image','date','branch_id','role' ,'User Status','worker_device_id','in_time','out_time','in_location_id','Att .status','status_updated_at','status_updated_by','approved_designation','Reason','status','work_hours'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15];
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

        return view('backend.reports.index', compact('html' ,'tsm','rsm','employees','roles'));
    }

    // user never logged in
    public function user_never_login(Datatables $datatables, Request $request){

        $tsm=User::where('role',5)->get();
        $rsm=User::where('role',6)->get();
        $employees=User::all();
        $roles = Role::whereIn('id',array(3,7,8))->get();
        $columns = [
            'employee_id',
            'name' => ['name' => 'user.name'],
            'created_at',
            'branch_id' => ['title' => 'Sole Id'],
            'role',
        ];

        // $tsm_value=$request->tsm;
        $rsm_value=$request->rsm;
        $employee=$request->employee;
        $role=$request->role;

        $tsm_value='666';


        if ($datatables->getRequest()->ajax()) {
            $query = User::whereNull('device_id')->whereNotIn('role',[1,2])->get();

            if($employee){
                $query = $query->where('worker_id', $employee); 
            }

            return $datatables->of($query) 
                ->addColumn('employee_id', function (User $data) {
                    return $data->emp_id;
                })
                ->addColumn('name', function (User $data) {
                    return $data->name;
                })
                ->addColumn('created_at', function (User $data) {
                    return $data->created_at->toDateString();
                })
                
                ->addColumn('branch_id', function (User $data) {
                    if(!isset($data->area_id)){return " ";}else{ if(isset($data->area->id)){return $data->area->id;} }
                })
                ->addColumn('role', function (User $data) {
                    return isset($data->userRole->display_name)? $data->userRole->display_name : '';
                })

                ->rawColumns(['employee_id','name','branch_id','role'])
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

        return view('backend.reports.userNeverLogin', compact('html' ,'tsm','rsm','employees','roles'));
    }
    // user never logged in end

// report obst start
    public function obst(Datatables $datatables, Request $request)
    {
        $tsm=User::where('role',5)->get();
        $rsm=User::where('role',6)->get();
        $employees=User::all();
        $roles = Role::whereIn('id',array(3,7,8))->get();
        $columns = [
            'employee_id',
            'name' => ['name' => 'user.name'],
            'date',
            'branch_id',
            'role',
            'in_time',
            // 'out_time',
            // 'work_hour',
            // 'over_time',
            // 'late_time',
            // 'early_out_time',
            'in_location_id' => ['name' => 'areaIn.name', 'title' => 'In Location'],
            // 'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location'],
            'in_lat_long',
            'status',
            'status_updated_at' => ['title' => 'Approved At'],
            'status_updated_by' => ['title' => 'Approved By'],
            'worker_device_id',
        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);

        $month=$request->monthly;
        $year=$request->yearly;
        // $month='2022-03';

        $start_month=Carbon::parse($month)->firstOfMonth()->toDateString();
        $end_month=Carbon::parse($month)->endOfMonth()->toDateString();

        $start_year=Carbon::create($year)->startOfYear()->toDateString();
        $end_year=Carbon::create($year)->endOfYear()->toDateString();

        // $tsm_value=$request->tsm;
        $rsm_value=$request->rsm;
        $employee=$request->employee;
        $role=$request->role;

        $tsm_value='666';
        // $user=TsmEmp::where('tsm_id',$tsm_value)->first();
        // dd($user);

        
        if ($datatables->getRequest()->ajax()) {
            $query = Attendance::with('user', 'areaIn', 'areaOut','attendanceStatus')
                ->select('attendances.*')->where('worker_role_id', '3');
        

            if ($from && $to) {
                $query = $query->whereBetween('date', [$from, $to]);
            }

            if($month){
                $query = $query->whereBetween('date', [$start_month, $end_month]);
            }

            if($year){
                $query = $query->whereBetween('date', [$start_year, $end_year]);
            }

            if($employee){
                $query = $query->where('worker_id', $employee); 
            }
            // if($role){
            //     $query = $query->where('worker_role_id', $role); 
            // }

            // if($tsm_value){
            //     $query=$query->where('worker_id',(TsmEmp::where('tsm_id',$tsm_value))->get());
            // }

            // worker
            // if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
            //     $query = $query->where('worker_id', Auth::user()->id);
            // }

            return $datatables->of($query) 
                ->addColumn('employee_id', function (Attendance $data) {
                    return $data->user->emp_id;
                })
                ->addColumn('name', function (Attendance $data) {
                    return $data->user->name;
                })
                ->addColumn('branch_id', function (Attendance $data) {
                    return $data->user->area_id==''?'-':$data->user->area_id;
                    // if(!isset($data->user->area_id)){return " ";}else{ if(isset($data->user->area->id)){return $data->user->area->id;} }
                })
                ->addColumn('role', function (Attendance $data) {
                    return $data->user->userRole->display_name;
                })
                ->addColumn('in_location_id', function (Attendance $data) {
                    // return $data->in_location_id == null ? '' : $data->areaIn->address;
                    if(!isset($data->in_location_id)){return " ";}else{ if(isset($data->areaIn->address)){return $data->areaIn->address;} }
                })
                // ->addColumn('out_location_id', function (Attendance $data) {
                //     return $data->out_location_id == null ? '' : $data->areaOut->address;
                // })
                ->addColumn('status', function (Attendance $data) {
                    return $data->attendanceStatus->name;
                })
                ->addColumn('status_updated_by', function (Attendance $data) {
                    return $data->status_updated_by == null ? '' : $data->attendanceUpdatedBy->name;
                })
                // ->rawColumns(['name', 'out_location_id', 'in_location_id','status','status_updated_by'])
                ->rawColumns(['employee_id','name','branch_id','role' ,'in_location_id','status','status_updated_by'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9,10];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[1,'desc'], [2,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'paging' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.reports.reportObst', compact('html' ,'tsm','rsm','employees','roles'));
    }
// report obst end

// report dst start
    public function dst(Datatables $datatables, Request $request)
    {
        $tsm=User::where('role',5)->get();
        $rsm=User::where('role',6)->get();
        $employees=User::all();
        $roles = Role::whereIn('id',array(3,7,8))->get();
        $columns = [
            'employee_id',
            'name' => ['name' => 'user.name'],
            'date',
            'branch_id',
            'role',
            'in_time',
            // 'out_time',
            // 'work_hour',
            // 'over_time',
            // 'late_time',
            // 'early_out_time',
            'in_location_id' => ['name' => 'areaIn.name', 'title' => 'In Location'],
            // 'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location'],
            'status',
            // 'status_updated_at',
            // 'status_updated_by',
            'worker_device_id',
        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);

        $month=$request->monthly;
        $year=$request->yearly;
        // $month='2022-03';

        $start_month=Carbon::parse($month)->firstOfMonth()->toDateString();
        $end_month=Carbon::parse($month)->endOfMonth()->toDateString();

        $start_year=Carbon::create($year)->startOfYear()->toDateString();
        $end_year=Carbon::create($year)->endOfYear()->toDateString();

        // $tsm_value=$request->tsm;
        $rsm_value=$request->rsm;
        $employee=$request->employee;
        $role=$request->role;

        $tsm_value='666';
        // $user=TsmEmp::where('tsm_id',$tsm_value)->first();
        // dd($user);


        if ($datatables->getRequest()->ajax()) {
            $query = Attendance::with('user', 'areaIn', 'areaOut','attendanceStatus')
                ->select('attendances.*')->where('worker_role_id', '8');
        

            if ($from && $to) {
                $query = $query->whereBetween('date', [$from, $to]);
            }

            if($month){
                $query = $query->whereBetween('date', [$start_month, $end_month]);
            }

            if($year){
                $query = $query->whereBetween('date', [$start_year, $end_year]);
            }

            if($employee){
                $query = $query->where('worker_id', $employee); 
            }
            // if($role){
            //     $query = $query->where('worker_role_id', $role); 
            // }

            // if($tsm_value){
            //     $query=$query->where('worker_id',(TsmEmp::where('tsm_id',$tsm_value))->get());
            // }

            // worker
            // if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
            //     $query = $query->where('worker_id', Auth::user()->id);
            // }

            return $datatables->of($query) 
                ->addColumn('employee_id', function (Attendance $data) {
                    return $data->user->emp_id;
                })
                ->addColumn('name', function (Attendance $data) {
                    return $data->user->name;
                })
                ->addColumn('branch_id', function (Attendance $data) {
                    return $data->user->area_id==''?'-':$data->user->area_id;
                    // if(!isset($data->user->area_id)){return " ";}else{ if(isset($data->user->area->id)){return $data->user->area->id;} }
                })
                ->addColumn('role', function (Attendance $data) {
                    return $data->user->userRole->display_name;
                })
                ->addColumn('in_location_id', function (Attendance $data) {
                    // return $data->in_location_id == null ? '' : $data->areaIn->address;
                    if(!isset($data->in_location_id)){return " ";}else{ if(isset($data->areaIn->address)){return $data->areaIn->address;} }
                })
                // ->addColumn('out_location_id', function (Attendance $data) {
                //     return $data->out_location_id == null ? '' : $data->areaOut->address;
                // })
                ->addColumn('status', function (Attendance $data) {
                    return $data->in_time==null?'Absent':'present';
                })
                ->addColumn('status_updated_by', function (Attendance $data) {
                    return $data->status_updated_by == null ? '' : $data->attendanceUpdatedBy->name;
                })
                // ->rawColumns(['name', 'out_location_id', 'in_location_id','status','status_updated_by'])
                ->rawColumns(['employee_id','name','branch_id','role' ,'in_location_id','status','status_updated_by'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[1,'desc'], [2,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'paging' => false,
                'bAutoWidth' => false,
                'Info' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.reports.reportDst', compact('html' ,'tsm','rsm','employees','roles'));
    }
// report dst end

// report boa start
    public function boa(Datatables $datatables, Request $request)
    {
        $tsm=User::where('role',5)->get();
        $rsm=User::where('role',6)->get();
        $employees=User::all();
        $roles = Role::whereIn('id',array(3,7,8))->get();
        $columns = [
            'employee_id',
            'name' => ['name' => 'user.name'],
            'date',
            'branch_id',
            'role',
            'in_time',
            // 'out_time',
            // 'work_hour',
            // 'over_time',
            // 'late_time',
            // 'early_out_time',
            'in_location_id' => ['name' => 'areaIn.name', 'title' => 'In Location'],
            // 'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location'],
            'status',
            // 'status_updated_at',
            // 'status_updated_by',
            'worker_device_id',
        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);

        $month=$request->monthly;
        $year=$request->yearly;
        // $month='2022-03';

        $start_month=Carbon::parse($month)->firstOfMonth()->toDateString();
        $end_month=Carbon::parse($month)->endOfMonth()->toDateString();

        $start_year=Carbon::create($year)->startOfYear()->toDateString();
        $end_year=Carbon::create($year)->endOfYear()->toDateString();

        // $tsm_value=$request->tsm;
        $rsm_value=$request->rsm;
        $employee=$request->employee;
        $role=$request->role;

        $tsm_value='666';
        // $user=TsmEmp::where('tsm_id',$tsm_value)->first();
        // dd($user);


        if ($datatables->getRequest()->ajax()) {
            $query = Attendance::with('user', 'areaIn', 'areaOut','attendanceStatus')
                ->select('attendances.*')->where('worker_role_id', '7');
        

            if ($from && $to) {
                $query = $query->whereBetween('date', [$from, $to]);
            }

            if($month){
                $query = $query->whereBetween('date', [$start_month, $end_month]);
            }

            if($year){
                $query = $query->whereBetween('date', [$start_year, $end_year]);
            }

            if($employee){
                $query = $query->where('worker_id', $employee); 
            }
            // if($role){
            //     $query = $query->where('worker_role_id', $role); 
            // }

            // if($tsm_value){
            //     $query=$query->where('worker_id',(TsmEmp::where('tsm_id',$tsm_value))->get());
            // }

            // worker
            // if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
            //     $query = $query->where('worker_id', Auth::user()->id);
            // }

            return $datatables->of($query) 
                ->addColumn('employee_id', function (Attendance $data) {
                    return $data->user->emp_id;
                })
                ->addColumn('name', function (Attendance $data) {
                    return $data->user->name;
                })
                ->addColumn('branch_id', function (Attendance $data) {
                    return $data->user->area_id==''?'-':$data->user->area_id;
                    // if(!isset($data->user->area_id)){return " ";}else{ if(isset($data->user->area->id)){return $data->user->area->id;} }
                })
                ->addColumn('role', function (Attendance $data) {
                    return $data->user->userRole->display_name;
                })
                ->addColumn('in_location_id', function (Attendance $data) {
                    // return $data->in_location_id == null ? '' : $data->areaIn->address;
                    if(!isset($data->in_location_id)){return " ";}else{ if(isset($data->areaIn->address)){return $data->areaIn->address;} }
                })
                // ->addColumn('out_location_id', function (Attendance $data) {
                //     return $data->out_location_id == null ? '' : $data->areaOut->address;
                // })
                // ->addColumn('status', function (Attendance $data) {
                //     return $data->in_time==null?'Absent':'present';
                // })
                ->addColumn('status_updated_by', function (Attendance $data) {
                    return $data->status_updated_by == null ? '' : $data->attendanceUpdatedBy->name;
                })
                // ->rawColumns(['name', 'out_location_id', 'in_location_id','status','status_updated_by'])
                ->rawColumns(['employee_id','name','branch_id','role' ,'in_location_id','status','status_updated_by'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[1,'desc'], [2,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                // 'pageLength' => 25,
                'paging' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.reports.reportBoa', compact('html' ,'tsm','rsm','employees','roles'));
    }
// report boa end
    /**
     * Fungtion show button for export or print.
     *
     * @param $columnsArrExPr
     * @return array[]
     */
    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Verticle Attendance";
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
           

            var formData_rsm_tsm = $("#rsm_tsm_filter").find("select").serializeArray();
            $.each(formData_rsm_tsm, function(i, obj){
                data[obj.name] = obj.value;
            });
CDATA;
    }
}
