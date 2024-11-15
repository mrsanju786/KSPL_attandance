<?php

namespace App\Http\Controllers\Backend\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Config;
use App\Models\User;
use App\Models\Area;
use App\Models\Holiday;
use App\Models\AssignRole;
use App\Models\State;
use App\Models\Attendance;
use App\Models\LeaveBalance;
use App\Models\Role;
use App\Models\Designation;
use Carbon\Carbon;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;

class LeaveBalanceController extends Controller
{
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
        $columns = [
            'id' => ['title' => 'Sr.No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],

            'emp_id'      =>['title'=>'Emp Id'],
            'user_id'     =>['title'=>'Emp Name'],
            'role'        =>['title'=>'Role'],
            'designation' =>['title'=>'Designation'],
            'paid_leaves'       =>['title'=>'Paid Leaves'],
            'casual_leaves'       =>['title'=>'Casual Leaves'],
            'sick_leaves'       =>['title'=>'Sick Leaves'],
            'leave_balance'  =>['title'=>'Leave Balance'],
            'assigned_leaves'  =>['title'=>'Leaves Assigned'],
            'year'        =>['title'=>'Year'],
            'status'         =>['title'=>'Status'],
            // 'created_at'     =>['title'=>'Created At'],
            'action'         => ['orderable' => false, 'searchable' => false],
           
        ];

        $roles = Role::whereNotIn('id',[1,2,4,9])->get();
       
       // $name  = $request->role;
        $year  = $request->year;
       // $month = $request->month;

        if ($datatables->getRequest()->ajax()) {
            $query = LeaveBalance::orderBy('id','asc');

            // if (!empty($request->role)) {
            //     $query = $query->whereHas('user', function ($q) use($name) {
            //         $q->where('role',$name);
            //    });
            // }
            if(!empty($request->year)){
                $query = $query->where('year', 'like', '%'.$request->year.'%');
            }
            // elseif(!empty($request->month)){
            //     $query = $query->where('month', 'like', '%'.$request->month.'%');
            // }
            else{
                $query = $query->get();
            }
           
            return $datatables->of($query)

            ->addColumn('emp_id', function (LeaveBalance $leave) {
                $username = User::where('id',$leave->user_id)->first();
                return  $username->emp_id ?? "-";
            })

            ->addColumn('user_id', function (LeaveBalance $leave) {
                $username = User::where('id',$leave->user_id)->first();
                return  $username->name ?? "-";
            })
            
            ->addColumn('role', function (LeaveBalance $leave) {
                $username = User::where('id',$leave->user_id)->first();
                return (!empty(AssignRole::where('role_id', $username->role)->where(['company_id' => Auth::user()->company_id])->first()->display_name)) ? AssignRole::where('role_id', $username->role)->where(['company_id' => Auth::user()->company_id])->first()->display_name : Role::where('id', $username->role)->first()->display_name;

            })
            ->addColumn('designation', function (LeaveBalance $leave) {
                $user = User::where('id',$leave->user_id)->first();
                $designation = Designation::where('id',$user->designation)->first();
                return  $designation->name  ?? "-";
            })
            // ->addColumn('month', function (LeaveBalance $leave) {
            //     return $leave->month ?? "-";
            // })

            ->addColumn('year', function (LeaveBalance $leave) {
                return $leave->year ?? "-";
            })
            ->addColumn('paid_leaves', function (LeaveBalance $leave) {
                return $leave->paid_leaves ?? "-";
            })
            ->addColumn('casual_leaves', function (LeaveBalance $leave) {
                return $leave->casual_leaves ?? "-";
            })
            ->addColumn('sick_leaves', function (LeaveBalance $leave) {
                return $leave->sick_leaves ?? "-";
            })
            ->addColumn('leave_balance', function (LeaveBalance $leave) {
                return $leave->leave_balance ?? "-";
            })
            ->addColumn('assigned_leaves', function (LeaveBalance $leave) {
                return $leave->assigned_leaves ?? $leave->leave_balance ?? "-";
            })
            ->addColumn('status', function (LeaveBalance $leave) {
                return $leave->status==1 ? 'Active' : "-" ;
            })

            // ->addColumn('created_at', function (LeaveBalance $leave) {
                
            //     return  date('d-m-Y',strtotime($leave->created_at)) ?? "-";
            // })

            ->addColumn('action', function (LeaveBalance $leave) {
             
                $routeEdit = route($this->getRoute() . '.edit', $leave->id);
                $routeDelete = route($this->getRoute() . '.delete', $leave->id);
                // Check is administrator
                if (Auth::user()->hasRole('administrator')) {

                    $button = '<div style="display: flex;">';
                    $button .= '<div style="padding-right:3px;"><a href="'.$routeEdit.'"><button class="btn btn-primary btn-sm" title="click to edit"><i class="fa fa-edit"></i></button></a></div>';
                    $button .= '<div style="padding-right:3px;"><a href="'.$routeDelete.'"><button class="btn btn-danger btn-sm confirm-button6" title="click to delete"><i class="fa fa-trash"></i></button></a></div>';
                       
                }
                return $button;
            })
            
            ->rawColumns(['emp_id','user_id','role','designation','paid_leaves','casual_leaves','sick_leaves','leave_balance','assigned_leaves','year','status','action'])
            
            ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[7,'asc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.leavebalance.index', compact('html'));
    }

    /**
     * Fungtion show button for export or print.
     *
     * @param $columnsArrExPr
     * @return array[]
     */
    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Leave Balance";
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
CDATA;
    }

   
    public function add()
    {
        return view('backend.leavebalance.create');
    }

    /**
     * Get named route depends on which user is logged in
     *
     * @return String
     */
    private function getRoute()
    {
        return 'leavebalance';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $new = $request->all();
        
        $this->validator($new, 'create')->validate();
        
        try {
            $user = User::where('emp_id',$new['user_id'])->where('status',1)->first();
           
            $leaveBalance = new LeaveBalance;
            $leaveBalance->user_id = $user->id;
            $leaveBalance->paid_leaves   = $new['paid_leaves'];
            $leaveBalance->casual_leaves   = $new['casual_leaves'];
            $leaveBalance->sick_leaves   = $new['sick_leaves'];
            $leaveBalance->leave_balance = $new['paid_leaves'] + $new['casual_leaves'] + $new['sick_leaves'];
            $leaveBalance->assigned_leaves = $new['paid_leaves'] + $new['casual_leaves'] + $new['sick_leaves'];
            // $leaveBalance->month   = $new['month'];
            $leaveBalance->year    = $new['year'];
            $leaveBalance->save();
            
            return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_LEAVEBALANCE_ADDED_MESSAGE'));
           
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_LEAVEBALANCE_MESSAGE'));
        } catch (Exception $e) {
             return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_LEAVEBALANCE_MESSAGE'));
        }
    }

    /**
     * Validator data.
     *
     * @param array $data
     * @param $type
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, $type)
    {
       
        return Validator::make($data, [
            'user_id'         => $type == 'create' ? 'required|unique:leave_balances,user_id' : "",
            // 'name'           => $type == 'create' ? 'required|max:255' : '',
            // 'leave_balance'  => $type == 'create' ? 'required' : '',
            // 'month'          => $type == 'create' ? 'required' : '',
            // 'year'           => $type == 'create' ? 'required' : '',
           
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
        $data = LeaveBalance::find($id);
        $user = User::where('id',$data->user_id)->where('status',1)->first();
        return view('backend.leavebalance.edit', [
            'data' => $data,
            'user' => $user
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $new = $request->all();
        
        try {
            
            $currentData = LeaveBalance::find($request->get('id'));
            $this->validator($new, 'update')->validate();
            $user = User::where('emp_id',$new['user_id'])->first();

            $currentData->user_id = $user->id;
            $currentData->paid_leaves   = $new['paid_leaves'];
            $currentData->casual_leaves   = $new['casual_leaves'];
            $currentData->sick_leaves   = $new['sick_leaves'];
            $currentData->leave_balance = $new['paid_leaves'] + $new['casual_leaves'] + $new['sick_leaves'];
            $currentData->assigned_leaves = $new['paid_leaves'] + $new['casual_leaves'] + $new['sick_leaves'];
            // $currentData->month   = $new['month'];
            $currentData->year    = $new['year'];
            $currentData->save();
            
            return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_UPDATE_LEAVEBALANCE_MESSAGE'));
            
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_LEAVEBALANCE_MESSAGE'));
        }
    }


     /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            
            $data = LeaveBalance::where('id',$id)->first();
            $data->delete();
            return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_DELETE_LEAVEBALANCE_MESSAGE'));
            
        } catch (Exception $e) {
            // delete failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_LEAVEBALANCE_MESSAGE'));
        }
    }
 
    //upload holiday List
    public function import()
    {
        return view('backend.leavebalance.import');
    }

       /**
     * Upload and import data from csv file.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function importData(Request $request)
    {
        $errorMessage = '';
        $errorArr = array();

    try {
            // If file extension is 'csv'
         if ($request->hasFile('import')) {
             $file = $request->file('import');

             // File Details
             $extension = $file->getClientOriginalExtension();

             // If file extension is 'csv'
             if ($extension == 'csv' || $extension == 'xlsx') {
                 $fp = fopen($file, 'rb');

                 $header = fgetcsv($fp, 0, ',');
                 $countheader = count($header);

                 // Check is csv file is correct format
                 if ($countheader==5 && in_array('emp_id', $header, true)  && in_array('paid_leaves', $header, true)  && in_array('casual_leaves', $header, true)
                 && in_array('sick_leaves', $header, true) && in_array('year', $header, true)) {
                     // Loop the row data csv

                    //  DB::beginTransaction();
                     while (($csvData = fgetcsv($fp)) !== false) {

                         $csvData = array_map('utf8_encode', $csvData);

                         // Row column length
                         $dataLen = count($csvData);
                         // Assign value to variables
                         $emp_id         = $csvData[0];
                         $paid_leaves  = $csvData[1];
                         $casual_leaves  = $csvData[2];
                         $sick_leaves  = $csvData[3];
                         $year           = $csvData[4];
                         $leave_balance  =  $paid_leaves +  $casual_leaves +  $sick_leaves;
                         $assigned_leaves  =  $paid_leaves +  $casual_leaves +  $sick_leaves;

                        //insert data
                        
                        $user = User::where('emp_id',$emp_id)->where('status',1)->first();

                        if(!empty($user)){
                            $leaveBalance = LeaveBalance::where('user_id',$user->id)->first();
                           
                            if(!empty($leaveBalance)){
                                
                                $leaveBalance->user_id          =$user->id;
                                $leaveBalance->paid_leaves    =$paid_leaves;
                                $leaveBalance->casual_leaves    =$casual_leaves;
                                $leaveBalance->sick_leaves    =$sick_leaves;
                                $leaveBalance->leave_balance    =$leave_balance;
                                $leaveBalance->assigned_leaves    =$assigned_leaves;
                                $leaveBalance->year             =$year;
                                $leaveBalance->save();
                            }else{
                                $LeaveBalance = new LeaveBalance;
                                $LeaveBalance->user_id          =$user->id;
                                $LeaveBalance->paid_leaves    =$paid_leaves;
                                $LeaveBalance->casual_leaves    =$casual_leaves;
                                $LeaveBalance->sick_leaves    =$sick_leaves;
                                $LeaveBalance->leave_balance    =$leave_balance;
                                $LeaveBalance->assigned_leaves    =$assigned_leaves;
                                $LeaveBalance->year             =$year;
                                $LeaveBalance->save();
                            }
                        }
                       
                     }
                    
                     if ($errorMessage == '') {
                         return redirect()->route($this->getRoute())->with('success', 'Imported was success!');
                     }
                     return redirect()->route($this->getRoute())->with('warning', 'Imported was success! <br><b>Note: We do not import this data data because</b><br>' . $errorMessage);
                 }
                 return redirect()->route($this->getRoute())->with('error', 'Import failed! You are using the wrong CSV format. Please use the CSV template to import your data.');
             }
             return redirect()->route($this->getRoute())->with('error', 'Please choose file with .CSV extension.');
         }

         return redirect()->route($this->getRoute())->with('error', 'Please select CSV file.');

    } catch (Exception $e) {
        // Create is failed
        return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
    }

    }
    
}
