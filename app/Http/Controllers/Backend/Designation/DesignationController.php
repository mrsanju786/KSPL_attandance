<?php

namespace App\Http\Controllers\Backend\Designation;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Config;
use Yajra\Datatables\Datatables; 
use Illuminate\Support\Facades\Log;

class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(){
        $data = new Designation;
        $data->form_action =  $this->getRoute() . '.create';
        $data->page_type = 'add';
        $data->button_text = 'Add';

        
        return view('backend.designation.form', [
            'data' => $data]);
    }

    public function getRoute(){
        return 'designation';
    }

    public function index(Datatables $datatables)
    { 
        $columns = [
            'id' => ['title' => 'Sr.No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'name' =>['title'=>'Designation Name'],
            'created_at',
            'updated_at',
            'action' => ['orderable' => false, 'searchable' => false]
        ];

        if($datatables->getRequest()->ajax()){ 
            
            if(Auth::user()->hasRole('administrator')){
                $designation = Designation::get();

            }
            return $datatables->of($designation)
                ->addColumn('action', function (Designation $data) {
                    $routeEdit = route($this->getRoute() . ".edit", $data->id);
                   // dd($routeEdit);
                    $routeDelete = route($this->getRoute() . ".delete", $data->id);

                    $button = '<div class="col-sm-12"><div class="row">';
                    if (Auth::user()->hasRole('administrator')) { // Check the role
                        $button .= '<div class="col-sm-6"><a href="'.$routeEdit.'"><button class="btn btn-primary"><i class="fa fa-edit"></i></button></a></div> ';
                        if ($data->status==1) {
                            $button .= '<div class="col-sm-6"><a href="'.$routeDelete.'" class="active-button"><button class="btn btn-success">Active</button></a></div>';
                        }else{
                            $button .= '<div class="col-sm-6"><a href="'.$routeDelete.'" class="inactive-button"><button class="btn btn-danger">Inactive</button></a></div>';
                        }
                        
                    } else {
                        $button = '<a href="#"><button class="btn btn-primary disabled"><i class="fa fa-edit"></i></button></a> ';
                        $button .= '<a href="#"><button class="btn btn-danger disabled"><i class="fa fa-trash"></i></button></a>';
                    }
                    $button .= '</div></div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->toJson();
                 
        }
        $columnsArrExPr = [0,1,2,3,4];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'order' => [[4,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.designation.index', compact('html'));
        
    }

    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Designation";
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
          //  dd($request->all());
            $validator = Validator::make($request->all(),[
                'designation' => 'required|string', 
            ]);

            if($validator->fails()){
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            if (Designation::where('name', 'like', '%' . $request->designation)->exists()) {
                return  redirect()->route('designation.add')->with('error', 'The entered designation already exist.');
            }  

            $data = new Designation;
            $data->name = $request->designation;
            $data->alias = $this->alias($request->designation);
            $data->save(); 
            return redirect()->route('designation')->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
        } catch (\Throwable $th) {
            Log::error($th);
            return  redirect()->route('branch.add')->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function alias($data){ 
        $name = str_replace(" ",'_',$data);
        return $name;
    }
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
        $data = Designation::find($id);
        $data->form_action = $this->getRoute(). '.update';
        $data->page_type = "edit";
        $data->button_text  = 'Update';
        //dd($data);
        return view('backend.designation.form', ['data' => $data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //dd($request->all());
        try {
            $validator = Validator::make($request->all(),[
                'designation' => 'required|string'
            ]);

            if($validator->fails()){
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            if (Designation::where('name', 'like', '%' . $request->designation)->exists()) {
                return  redirect()->route($this->getRoute())->with('error', 'The entered designation already exist.');
            } 

            $data = Designation::find($request->id);
            $data->name = $request->designation;
            $data->alias = $this->alias($request->designation);
            $data->save();
            return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
  

        } catch (\Throwable $th) {
            Log::error($th);
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = Designation::find($id);
            if($data){
                $newStatus = $data->status === 1 ? 0 : 1;
                $data->status = $newStatus;
                $data->save();

                $message = $newStatus === 1 ? 'Activated' : 'Deactivated';
                return redirect()->route($this->getRoute())->with('success', "Designation has been $message Successfully.");
            } else {
                return redirect()->route($this->getRoute())->with('error', "Designation not found");
            }
        } catch (\Throwable $th) {
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_MESSAGE'));
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route($this->getRoute())->with('error', Config::get('const.ERROR_FOREIGN_KEY'));
        }
    }
}
