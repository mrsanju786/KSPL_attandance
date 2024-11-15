<?php

namespace App\Http\Controllers\Backend\Leave;


use Auth;
use Excel;
use Config;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Area;
use Carbon\CarbonPeriod;
use App\Models\Leave;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class LeaveController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Datatables $datatables , Request $request)
    {
        $columns = [
            'id' => ['title' => 'Sr.No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],

            'emp_id'   =>['title'=>'Emp Id'],
            'user_id'    =>['title'=>'Emp Name'],
           
            'role'       =>['title'=>'Role'],
            'leave_type' =>['title'=>'Leave Type'],
            'leave_duration' =>['title'=>'Leave Duration'],
            'created_at' =>['title'=>'Applied At'],
            'from_date'  =>['title'=>'From Date'],
            'to_date'    =>['title'=>'To Date'],
           
            'remark'     =>['title'=>'Leave Reason'],
            'status'     =>['title'=>'Status'],
            'approved_by'=>['title'=>'Approved By'],
            'approved_designation'=>['title'=>'Approved Designation'],
            'head_remark'     =>['title'=>'Remark'],
            'approved_at'=>['title'=>'Approved At'],
            
            
           
        ];
        $roles = Role::whereNotIn('id',[1,2,4,9])->get();
       
        $name = $request->role;
       
        if ($datatables->getRequest()->ajax()) {
            $query = Leave::with('user')->orderBy('id','desc');
            
            if (!empty($request->role)) {
                $query = $query->whereHas('user', function ($q) use($name) {
                    $q->where('role',$name);
               });
            }else{
                $query = $query->get();
            }

            return $datatables->of($query)
                
                ->addColumn('user_id', function (Leave $leave) {
                    $username = User::where('id',$leave->user_id)->first();
                    return  $username->name ?? "-";
                })
                ->addColumn('emp_id', function (Leave $leave) {
                    $username = User::where('id',$leave->user_id)->first();
                    return  $username->emp_id ?? "-";
                })

                ->addColumn('role', function (Leave $leave) {
                    $username = User::where('id',$leave->user_id)->first();
                    return  $username->userRole->display_name?? "-";
                })
                ->addColumn('leave_type', function (Leave $leave) {
                    return $leave->leave_type ?? "-";
                })
                ->addColumn('leave_duration', function (Leave $leave) {
                    return $leave->leave_duration ?? "-";
                })
                ->addColumn('created_at', function (Leave $leave) {
                    
                    return  date('d-m-Y',strtotime($leave->created_at)) ?? "-";
                })
                ->addColumn('from_date', function (Leave $leave) {
                    return date('d-m-Y',strtotime($leave->from_date)) ?? "-";
                })
                ->addColumn('to_date', function (Leave $leave) {
                    return date('d-m-Y',strtotime($leave->to_date)) ?? "-";
                })
                ->addColumn('remark', function (Leave $leave) {
                    return $leave->remark ?? "-";
                })

                ->addColumn('head_remark', function (Leave $leave) {
                    return $leave->head_remark ?? "-";
                })
                ->addColumn('status', function (Leave $leave) {  
                    $username = User::where('id',$leave->user_id)->first();
                    if($username->role ==7){
                        return "Paid Leave";
                    }elseif($username->role ==8){
                        return "Paid Leave";
                    }
                    if($leave->status ==0){
                        return '<span class="badge badge-danger" >Pending</span>';
                    }else if($leave->status ==1){
                        return "Approved";
                    }else{
                        return "Rejected";
                    }
                      
                })
                ->addColumn('approved_by', function (Leave $leave) {
                    $usernames = User::where('id',$leave->approved_by)->first();
                    return  $usernames->name ?? "-";
                })

                ->addColumn('approved_designation', function (Leave $leave) {
                    $user = User::where('id',$leave->approved_by)->first();
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
                ->addColumn('approved_at', function (Leave $leave) {
                    if(!empty($leave->approved_at)){
                        return  date('d-m-Y',strtotime($leave->approved_at)) ?? "-";
                    }else{
                        return "-";
                    }
                    
                })

                
                
                ->rawColumns(['id','emp_id','user_id','role', 'leave_type', 'leave_duration', 'created_at','from_date','to_date','remark','status','approved_by','approved_designation','head_remark','approved_at'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9,10,11,12,13];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);
            
        return view('backend.leave.index', compact('html','roles','name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Leave";
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
}
