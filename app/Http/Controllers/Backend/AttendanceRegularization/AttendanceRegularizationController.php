<?php

namespace App\Http\Controllers\Backend\AttendanceRegularization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AttendanceRegularization;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Role;
use App\Models\AssignRole;
use App\Models\TypeOfRegularization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Mail;
use App\Mail\AttendanceRegularizationResponse;
use Auth;

class AttendanceRegularizationController extends Controller
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

    public function attendanceRegularList(Datatables $datatables, Request $request){
        $roles=Role::whereIn('id',[3,5,6,7,8])->get();
        $columns = [
            'employee_id'  => ['title'=>'Employee ID'],
            'name' => ['title'=>'Employee Name'],
            'date',
            'type_of_regularization' => ['title'=>'Type Of Regularization'],
            'in_time',
            'out_time',
            'role',
            'reason',
            'action' => ['orderable' => false, 'searchable' => false]

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
            $query = AttendanceRegularization::with('user')
            ->select('attendance_regularizations.*')
            ->where('status', 0)
            ->whereHas('user', function ($q) {
                $q->where('status', 1); // This condition is applied to the 'users' table
            });
        

            if ($from && $to) {
                $query = $query->whereBetween('attendance_date', [$from, $to]);
            }

            if($month){
                $date_arr = explode("-",$month);
                $query = $query->whereMonth('attendance_date', $date_arr[1])->whereYear('attendance_date', $date_arr[0]);
            }
            
            $query = $query->orderBy('attendance_regularizations.created_at','desc')->get();
            
            return $datatables->of($query)
                ->addColumn('employee_id', function (AttendanceRegularization $data) {
                    
                    return $data->user->emp_id;
                })
                ->addColumn('name', function (AttendanceRegularization $data) {
                    return $data->user->name;
                })
                ->addColumn('date', function (AttendanceRegularization $data) {
                    return date('d-m-Y',strtotime($data->attendance_date));
                })
                ->addColumn('type_of_regularization', function (AttendanceRegularization $data) {
                       $typesOfRegular = TypeOfRegularization::find($data->regularization_id);
                    return $typesOfRegular->name ?? "NA";
                })
                ->addColumn('in_time', function (AttendanceRegularization $data) {
                    return $data->in_time;
                })
                ->addColumn('out_time', function (AttendanceRegularization $data) {
                    return $data->out_time ?? "NA";
                })
                ->addColumn('role', function (AttendanceRegularization $data) {
                    return (!empty(AssignRole::where('role_id', $data->user->userRole->id)->where(['company_id' => Auth::user()->company_id])->first()->display_name)) ? AssignRole::where('role_id', $data->user->userRole->id)->where(['company_id' => Auth::user()->company_id])->first()->display_name : Role::where('id', $data->user->userRole->id)->first()->display_name;

                })
                ->addColumn('reason', function (AttendanceRegularization $data) {
                    return '<button class="btn btn-info btn-sm view-reason" data-reason="' . htmlspecialchars($data->reason, ENT_QUOTES, 'UTF-8') . '">
                                <i class="fa fa-eye"></i>
                            </button>';
                })                
                ->addColumn('action', function ($data) {

                    $button = '<div class="col-sm-12"><div class="row">';
                    
                    if (Auth::user()->hasRole('administrator')) {
                        // Approve link
                        $button .= '<div class="col-sm-4 mr-2">
                                <a href="' . url('/update-status/' . $data['id'] . '/approved') . '" class="btn btn-success confirm-status-update">
                                    <i class="fa fa-check"></i>
                                </a>
                            </div>';
                        // Reject link
                        $button .= '<div class="col-sm-4">
                                        <a href="' . url('/update-status/' . $data['id'] . '/rejected') . '" class="btn btn-danger confirm-status-update">
                                            <i class="fa fa-times"></i>
                                        </a>
                                    </div>';
                    } else {
                        $button .= '<div class="col-sm-4">
                                        <button class="btn btn-success disabled">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </div>';
                        $button .= '<div class="col-sm-4">
                                        <button class="btn btn-danger disabled">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>';
                    }
                    
                    $button .= '</div></div>';
                    
                    return $button;
                })
                
                // ->rawColumns(['name','role' ,'out_location_id', 'in_location_id','status','status_updated_by'])
                ->rawColumns(['employee_id','name','date','role','reason','in_time','out_time','action'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9,10,11];
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

        return view('backend.attendanceRegularization.attendance-regularization', compact('html' ,'roles'));
    }

    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Attendance Regularization";
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

    public function scriptMinifiedJs()
    {
        // Script to minify the ajax
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
            return redirect()->route('attendance-regularization')->with('success', 'Status Updated Successfully.');
        } else {
            return redirect()->route('attendance-regularization')->with('error', 'Somthing went wrong.!!');
        }
    }
}
