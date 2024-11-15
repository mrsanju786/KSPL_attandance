<?php

namespace App\Http\Controllers\Backend\OutDoor;

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
use Yajra\Datatables\Datatables;

class OutDoorController extends Controller
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
           // 'role'       =>['title'=>'Role'],
            'created_at' =>['title'=>'Applied At'],
            'from_date'  =>['title'=>'From Date'],
            'to_date'    =>['title'=>'To Date'],
            'od_type'     =>['title'=>'OD Type'],
            'remark'     =>['title'=>'OD Reason'],
            'status'     =>['title'=>'Status'],
            'approved_by'=>['title'=>'Approved By'],
            'approved_designation'=>['title'=>'Approved Designation'],
            'head_remark'     =>['title'=>'Remark'],
            'approved_at'=>['title'=>'Approved At'],
            
            
           
        ];
      //  $roles = Role::whereNotIn('id',[1,2,4,9])->get();
       
       // $name = $request->role;
       
        if ($datatables->getRequest()->ajax()) {
            $query = OutDoor::with('user')->orderBy('id','desc');
            
            // if (!empty($request->role)) {
            //     $query = $query->whereHas('user', function ($q) use($name) {
            //         $q->where('role',$name);
            //    });
            // }else{
            // }

            $query = $query->get();

            return $datatables->of($query)
                
                ->addColumn('user_id', function (OutDoor $outdoor) {
                    $username = User::where('id',$outdoor->user_id)->first();
                    return  $username->name ?? "-";
                })
                ->addColumn('emp_id', function (OutDoor $outdoor) {
                    $username = User::where('id',$outdoor->user_id)->first();
                    return  $username->emp_id ?? "-";
                })

                // ->addColumn('role', function (OutDoor $outdoor) {
                //     $username = User::where('id',$outdoor->user_id)->first();
                //     return  $username->userRole->display_name?? "-";
                // })
               
                ->addColumn('created_at', function (OutDoor $outdoor) {
                    
                    return  date('d-m-Y',strtotime($outdoor->created_at)) ?? "-";
                })
                ->addColumn('from_date', function (OutDoor $outdoor) {
                    return date('d-m-Y',strtotime($outdoor->from_date)) ?? "-";
                })
                ->addColumn('to_date', function (OutDoor $outdoor) {
                    return date('d-m-Y',strtotime($outdoor->to_date)) ?? "-";
                })
                ->addColumn('od_type', function (OutDoor $outdoor) {
                    return $outdoor->od_type ?? "-";
                })
                ->addColumn('remark', function (OutDoor $outdoor) {
                    return $outdoor->remark ?? "-";
                })

                ->addColumn('head_remark', function (OutDoor $outdoor) {
                    return $outdoor->head_remark ?? "-";
                })
                ->addColumn('status', function (OutDoor $outdoor) {  
                    $username = User::where('id',$outdoor->user_id)->first();
                    // if($username->role ==7){
                    //     return "Paid Leave";
                    // }elseif($username->role ==8){
                    //     return "Paid Leave";
                    // }
                    if($outdoor->status ==0){
                        return '<span class="badge badge-danger" >Pending</span>';
                    }else if($outdoor->status ==1){
                        return "Approved";
                    }else{
                        return "Rejected";
                    }
                      
                })
                ->addColumn('approved_by', function (OutDoor $outdoor) {
                    $usernames = User::where('id',$outdoor->approved_by)->first();
                    return  $usernames->name ?? "-";
                })

                ->addColumn('approved_designation', function (OutDoor $outdoor) {
                    $user = User::where('id',$outdoor->approved_by)->first();
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
                ->addColumn('approved_at', function (OutDoor $outdoor) {
                    if(!empty($outdoor->approved_at)){
                        return  date('d-m-Y',strtotime($outdoor->approved_at)) ?? "-";
                    }else{
                        return "-";
                    }
                    
                })

                
                
                ->rawColumns(['id','emp_id','user_id','role', 'created_at','from_date','to_date','od_type','remark','status','approved_by','approved_designation','head_remark','approved_at'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9,10,11,12];
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
            
        return view('backend.outdoor.index', compact('html'));
    }

    
    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "OD";
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