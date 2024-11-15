<?php

namespace App\Http\Controllers\Backend\Helpdesk;

use Auth;
use Config;
use App\Models\User;
use App\Models\Helpdesk;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;

class HelpdeskController extends Controller
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
        $columns = [
            'ticket_no'=>['title'=>'Ticket No.'],
            'name',
            'emp_id',
            'topic',
            'description',
            'date'=>['title'=>'Ticket Open Date'],
            'images'=>['title'=>'<i class="fa fa-image fa-2x" aria-hidden="true"></i>'],
            'status'=>['title'=>'Ticket Status'],
            'action'=>['orderable' => false, 'searchable' => false],
            'updated_at'=>['title'=>'Status Date'],
            'remark'=>['title'=>'Status Remark'],
            'updated_by'=>['title'=>'Updated By'],
            
            // 'in_location_id' => ['name' => 'areaIn.name', 'title' => 'In Location'],
            // 'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location']
        ];

        $from = date($request->dateFrom);
        $to = date('Y-m-d',strtotime($request->dateTo. ' +1 day'));

        if ($datatables->getRequest()->ajax()) {
            $query = Helpdesk::all();

            if ($from && $to) {
                $query = $query->whereBetween('created_at', [$from, $to]);
                // $query = $query->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to);
            }
            $query = $query->sortByDesc('created_at');
            return $datatables->of($query)
                ->addColumn('ticket_no', function (Helpdesk $data) {
                    if($data->ticket_no){
                        return $data->ticket_no;
                    }else{
                        return 'NA';
                    }
                })
                ->addColumn('name', function (Helpdesk $data) {
                    // return User::where('id',$data->user_id)->first()->name;
                    $u_name = User::where('id',$data->user_id)->first();
                    if($u_name != NULL){
                        return $u_name->name;
                    }else{
                        return 'NA';
                    }
                })
                ->addColumn('emp_id', function (Helpdesk $data) {
                    // return User::where('id',$data->user_id)->first()->name;
                    $emp_info = User::where('id',$data->user_id)->first();
                    if($emp_info != NULL){
                        return $emp_info->emp_id;
                    }else{
                        return 'NA';
                    }
                })
                ->addColumn('images', function (Helpdesk $data) {
                    if($data->images!=null){
                        $getAssetFolder = asset('uploads/'.$data->images);
                        $getAssetFolderLogo = asset('img/default.png');
                        return '<img src="'.$getAssetFolderLogo.'" width="50px" class="img-circle elevation-2" data-toggle="modal" style="border-radius: 0px;"  data-id="'.$getAssetFolder.'">';
                    }
                    else{
                        return '';
                    }
                    
                })
                ->addColumn('date', function (Helpdesk $data) {
                    
                    return date('d-m-Y',strtotime($data->created_at)) ?? "-";
                        
                })
                ->addColumn('status', function (Helpdesk $data) {
                    if($data->status == 0){
                        return 'Open';
                    }elseif($data->status == 1){
                        return 'In Progress';
                    }elseif($data->status == 2){
                        return 'On Hold';
                    }else{
                        return 'Close';
                    }
                })
                ->addColumn('updated_at', function (Helpdesk $data) {
                    if($data->updated_at){
                        return date('d-m-Y',strtotime($data->updated_at));
                    }else{
                        return 'NA';
                    }
                })
                ->addColumn('updated_by', function (Helpdesk $data) {
                    if(User::where('id',$data->updated_by)->exists()){
                        $updated_name = User::where('id',$data->updated_by)->first();
                        if($updated_name != NULL){
                            return $updated_name->name;
                        }else{
                            return 'NA';
                        }
                    }else{
                        return "-";
                    }
                })
                ->addColumn('remark', function (Helpdesk $data) {
                    if($data->remark){
                        return $data->remark;
                    }else{
                        return 'NA';
                    }
                })
                ->addColumn('action', function (Helpdesk $data) {
                   
                    $routeActive = route($this->getRoute() . '.open', $data->id);
                    $routeDeactive = route($this->getRoute() . '.closed', $data->id);
                   

                    // Check is administrator
                    if (Auth::user()->hasRole('administrator')) {
                        $button = '<div style="display: flex;">';
                        
                        
                        if($data->status == 0){
                            $button .= '<div><a href="'.$routeDeactive.'" class="confirm-button4" style="padding-right:5px;"><button class="btn btn-primary btn-sm" title="click to closed"><i class="fa fa-ban" aria-hidden="true"></i></button></a></div>';
                        }
                       //activate button
                        if($data->status == 3){
                            $button .= '<div><a href="'.$routeActive.'" class="confirm-button5" style="padding-right:5px;" ><button class="btn btn-primary  btn-sm" title="click to open"><i class="fa fa-user-slash"></i></button></a></div>';
                           
                        }
                        
                    } else {
                        $button .= '<a href="#"><button class="btn btn-primary disabled"><i class="fa fa-edit"></i></button></a> ';
                        $button .= '<a href="#"><button class="btn btn-danger disabled"><i class="fa fa-trash"></i></button></a>';
                    }
                    return $button;
                })
                ->rawColumns(['ticket_no','name','emp_id','topic','description','date','images','status','action','updated_at','remark','updated_by'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9,10,11,12];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[0,'desc'],[9,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.helpdesk.index', compact('html'));
    }

    /**
     * Fungtion show button for export or print.
     *
     * @param $columnsArrExPr
     * @return array[]
     */
    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Helpdesk";
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

    private function getRoute()
    {
        return 'helpdesk';
    }

    public function Closed($id)
    {
        $data = Helpdesk::find($id);
        // $data->status = 3;
        // $data->save();

        return view('backend.helpdesk.remark', [
            'data'   => $data,
        ]);
    }
    /**
     * update status to open start
     * */
    public function Open($id){
        $data = Helpdesk::find($id);
        $data->status = 0;
        $data->save();

        //user status success
        return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_HELPDESK_OPEN_MESSAGE'));
    }
   
    //remark save
    public function Remark(Request $request ,$id)
    {
        $input = $request->all();
       
        $user = Helpdesk::where('id',base64_decode($id))->first();
        $user->remark     = $input['remark'];
        $user->updated_by = Auth::user()->id;
        $user->status = 3;
        $user->save();
        if($user){
            return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_HELPDESK_CLOSE_MESSAGE'));
        }else{
            return redirect()->back();
        }
        
        
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

}
