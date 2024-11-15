<?php

namespace App\Http\Controllers\Backend\Holiday;

use Auth;
use Config;
use App\Models\User;
use App\Models\Area;
use App\Models\Holiday;
use App\Models\State;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use File;

class HolidayController extends Controller
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
            // 'name' => ['name' => 'user.name'],
            'date'=>['title'=>'Date'],
            'name' => ['title' => 'Holiday Name'],
            'state'=>['title'=>'State'],
           
            // 'action' => ['orderable' => false, 'searchable' => false],
            // 'to_date',
           
            // 'status',
            // 'approved_by',
            // 'created_by',
            // 'created_at',
            // 'action' => ['orderable' => false, 'searchable' => false],
            // 'in_location_id' => ['name' => 'areaIn.name', 'title' => 'In Location'],
            // 'out_location_id' => ['name' => 'areaOut.name', 'title' => 'Out Location']
        ];

        // $from = date($request->dateFrom);
        // $to = date($request->dateTo);
        $region = $request->regions;

        // $region_ids = Holiday::groupBy('area_id')->get('area_id')->toArray();
        // $area = Area::whereIn('id',$region_ids)->get();
        $state = Holiday::orderBy('id','desc')->groupBy('state')->orderBy('id','asc')->get();
        if ($datatables->getRequest()->ajax()) {
            $query = Holiday::orderBy('id','asc')->orderBy('state_id','asc');

            if ($region) {
                $query = $query->where('state', $region);
            }

            // // worker
            // if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
            //     $query = $query->where('worker_id', Auth::user()->id);
            // }
            $query = $query->get();

            return $datatables->of($query)
                ->addColumn('name', function (Holiday $data) {
                    return $data->name ?? 'NA';
                })
                ->addColumn('date', function (Holiday $data) {
                    return date('d-m-Y',strtotime($data->date)) ?? 'NA';
                })

                ->addColumn('state', function (Holiday $data) {
                    return $data->state ?? 'NA';
                })
                // ->addColumn('to_date', function (Holiday $data) {
                //     return $data->to_date;
                // })
                // ->addColumn('area_id', function (Holiday $data) {
                //     if(Area::where('id',$data->area_id)->exists()){
                //         $area_name = Area::where('id',$data->area_id)->first();
                //         return $area_name->address;
                //     }else{
                //         return 'NA';
                //     }
                // })

                // ->addColumn('action', function (Holiday $data) {
                    // $routeEdits = route($this->getRoute() . '.edit', $data->id);
                    
                    // // Check is administrator
                    // if (Auth::user()->hasRole('administrator')) {
                    //     $button = '<div style="display: flex;">';
                    //     $button .= '<div style="padding-right:3px;"><a href="'.$routeEdits.'"><button class="btn btn-primary btn-sm" title="click to edit"><i class="fa fa-edit"></i></button></a></div>';
                        
                        
                    // }
                    // return $button;
                // })
                // ->addColumn('status', function (Holiday $data) {
                //     if($data->status==0){
                //         return 'Pending';
                //     }elseif($data->status==1){
                //         return 'Approved';
                //     }else{
                //         return 'Rejected';
                //     }
                // })
                // ->addColumn('approved_by', function (Holiday $data) {
                //     if(User::where('id', $data->approved_by)->exists()) {
                //         return User::where('id', $data->approved_by)->first()->name;
                //     } else {
                //         return '-';
                //     }
                // })
                // ->addColumn('approved_at', function (Holiday $data) {
                //     if(User::where('id', $data->approved_at)->exists()) {
                //         return User::where('id', $data->approved_at)->first()->name;
                //     } else {
                //         return '-';
                //     }
                // })
                // ->addColumn('created_by', function (Holiday $data) {
                //     if(User::where('id', $data->created_by)->exists()){
                //         return User::where('id', $data->created_by)->first()->name;
                //     } else {
                //         return '-';
                //     }
                // })
                // ->addColumn('created_at', function (Holiday $data) {
                //     return $data->created_at;
                // })

                // ->addColumn('action', function (Holiday $data) {
                //     // $routeEdit = route($this->getRoute() . '.edit', $data->id);
                    
                //     // $button = '<div style="display: flex;">';
                //     // $button .= '<div style="padding-right:3px;"><a href="'.$routeEdit.'"><button class="btn btn-primary btn-sm" title="click to edit"><i class="fa fa-edit"></i></button></a></div>';
                        
                        
                    
                //     return $button;
                // })
                // ->orderColumn('id', 'asc')
                ->rawColumns(['name','date','state','action'])
                
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[3,'asc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.holiday.index', compact('html','state','region'));
    }

    /**
     * Fungtion show button for export or print.
     *
     * @param $columnsArrExPr
     * @return array[]
     */
    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Holiday";
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
        $data = new Holiday();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Add';

        return view('backend.holiday.form', [
            'data' => $data,
            'area_id'=>Area::orderBy('id')->pluck('address', 'id'),
            'state'   =>State::get(),
           
        ]);
    }

    /**
     * Get named route depends on which user is logged in
     *
     * @return String
     */
    private function getRoute()
    {
        return 'holiday';
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
            $state = State::where('id',$new['state_id'])->first();
            $new['state'] = $state->name;
            $createNew = Holiday::create($new);
            if ($createNew) {
               
                $createNew->save();

               
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_HOLIDAY_ADDED_MESSAGE'));
            }

            // DB::commit();
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        } catch (Exception $e) {
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
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
           
            'name'         => $type == 'create' ? 'required' : "",
           
            'state_id'        => $type == 'create' ? 'required|max:255' : '',
            'date'          => $type == 'create' ? 'required|max:255' : '',
           
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
        $data = Holiday::find($id);
        
        $data->form_action = $this->getRoute() . '.update';
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Update';


        return view('backend.holiday.form', [
            'data' => $data,
            'area_id'=>Area::orderBy('id')->pluck('address', 'id'),
            'state'  =>State::get(),
           
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
            $state        = State::where('id',$new['state_id'])->first();
            $new['state'] = $state->name;
            $currentData = Holiday::find($request->get('id'));
            if ($currentData) {
                $this->validator($new, 'update')->validate();
                
                $currentData->update($new);

                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_UPDATE_HOLIDAY_MESSAGE'));
            }

            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }
 
    //upload holiday List
    public function import()
    {
        $data = new Holiday();
        $data->form_action = $this->getRoute() . '.importData';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Import';

        return view('backend.holiday.import', [
            'data' => $data,
        ]);
    }

    // public function importData(Request $request) 
    // {
    //     $validatedData = $request->validate([
 
    //        'file' => 'required',
 
    //     ]);
 
    //     Excel::import(new HolidayImport,request()->file('file'));
 
            
    //     return redirect()->route($this->getRoute())->with('success', 'Imported was success!');
    // }

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
                 if ($countheader==3 && in_array('name', $header, true) && in_array('state', $header, true)
                 && in_array('date', $header, true)) {
                     // Loop the row data csv

                    //  DB::beginTransaction();
                     while (($csvData = fgetcsv($fp)) !== false) {

                         $csvData = array_map('utf8_encode', $csvData);

                         // Row column length
                         $dataLen = count($csvData);
                         // Assign value to variables
                         $state = $csvData[0];
                         $date  = $csvData[1];
                         $name  = $csvData[2];
                         
                        //insert data
                        $stateList = State::where('name',$state)->get();
                       
                        if($stateList != null){
                           
                            foreach($stateList as $value){
                                if(!empty($value->name)){
                                    $holiday = new Holiday;
                                    $holiday->name     =$name;
                                    $holiday->state    =$state;
                                    $holiday->state_id =$value->id;
                                    $holiday->date     =date('Y-m-d',strtotime($date));
                                    $holiday->save();
                                }
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
