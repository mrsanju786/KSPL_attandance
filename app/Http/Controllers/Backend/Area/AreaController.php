<?php

namespace App\Http\Controllers\Backend\Area;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;
use Yajra\Datatables\Datatables;
use App\Models\Area;
use App\Models\Location_coordinate;
use Auth;
use Config;
use DB;

class AreaController extends Controller
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
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'Sr.No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'name' =>['title'=>'Name / Sole ID'],
            'address',
            'lat' =>['title'=>'Latitude'],
            'long' =>['title'=>'Longitude'],
            'created_at',
            'updated_at',
            'action' => ['orderable' => false, 'searchable' => false]
        ];
       
        if ($datatables->getRequest()->ajax()) {

            if(Auth::user()->hasRole('Level1(TSM)')){
                 $areas=Auth::user()->areas;
            }
            else{
                $areas=Area::get();
               
            }
            return $datatables->of($areas)
                ->addColumn('lat', function (Area $data) {
                    
                    $loc_cordinate = Location_coordinate::where('area_id', $data->id)->first();
                    
                    if($loc_cordinate){
                        return $loc_cordinate->lat;
                    }else{
                        return 'NA';
                    }
                })
                ->addColumn('long', function (Area $data) {
                    
                    $loc_cordinate = Location_coordinate::where('area_id', $data->id)->first();
                    
                    if($loc_cordinate){
                        return $loc_cordinate->long;
                    }else{
                        return 'NA';
                    }
                })
                ->addColumn('created_at', function (Area $data) {
                   return date('d-m-Y',strtotime($data->created_at)) ?? "-";
                })
                ->addColumn('updated_at', function (Area $data) {
                    return date('d-m-Y',strtotime($data->updated_at)) ?? "-";
                 })
                ->addColumn('action', function (Area $data) {
                    $routeEdit = route($this->getRoute() . ".edit", $data->id);
                    $routeDelete = route($this->getRoute() . ".delete", $data->id);

                    $button = '<div class="col-sm-12"><div class="row">';
                    if (Auth::user()->hasRole('administrator') || Auth::user()->hasRole('Level1(TSM)') ||Auth::user()->hasRole('admin') ) { // Check the role
                        $button .= '<div class="col-sm-6"><a href="'.$routeEdit.'"><button class="btn btn-primary"><i class="fa fa-edit"></i></button></a></div> ';
                        $button .= '<div class="col-sm-6"><a href="'.$routeDelete.'" class="delete-button"><button class="btn btn-danger"><i class="fa fa-trash"></i></button></a></div>';
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

        $columnsArrExPr = [0,1,2,3,4,5,6];
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

        return view('backend.areas.index', compact('html'));
    }

    /**
     * Fungtion show button for export or print.
     *
     * @param $columnsArrExPr
     * @return array[]
     */
    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Area";
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
    public function add()
    {
        $data = new Area();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Add';

        $statement = DB::select("SHOW TABLE STATUS LIKE 'areas'");
        $data->id = $statement[0]->Auto_increment;

        return view('backend.areas.form', [
            'data' => $data,
        ]);
    }

    /**
     * Get named route depends on which user is logged in
     *
     * @return String
     */
    private function getRoute()
    {
        return 'areas';
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
            $createNew = Area::create($new);
            if ($createNew) {
                // below line 177 new added 
                $createNew->company_id = 1;
                $createNew->save();

                // get last inserted id new added
                $area_id = $createNew->id;

                // save lat long new added
                $cordinate =  new Location_coordinate;
                $cordinate->lat = $request->lat;
                $cordinate->long = $request->long;
                $cordinate->radius = $request->radius;
                $cordinate->area_id = $area_id;
                $cordinate->save();

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Create new area");

                // Create is successful, back to list
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
            }

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
        // Determine if password validation is required depending on the calling
        return Validator::make($data, [
            // Validator
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
        $data = Area::find($id);
        $data->form_action = $this->getRoute() . '.update';

        // fetch lat long from location_coordinate table
        $location_data = Location_coordinate::where('area_id',$id)->first();
        $data->lat = isset($location_data->lat)? $location_data->lat:NULL;
        $data->long = isset($location_data->long)? $location_data->long:NULL;
        $data->radius = isset($location_data->radius)? $location_data->radius:NULL;
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Update';

        return view('backend.areas.form', [
            'data' => $data,
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
            $currentData = Area::find($request->get('id'));
            if ($currentData) {
                $this->validator($new, 'update')->validate();

                // Update
                $currentData->update($new);

                // update location coordinate
                $cord_data = Location_coordinate::where('area_id',$request->get('id'))->first();

                if(isset($cord_data)){
                    $cord_data->update(['lat' => $request->lat,'long' => $request->long,'radius' => $request->radius]);
                }else{
                    $locationLatLong = new Location_coordinate;
                    $locationLatLong->lat     = $request->lat;
                    $locationLatLong->long    = $request->long;
                    $locationLatLong->radius    = $request->radius;
                    $locationLatLong->area_id = $request->get('id');
                    $locationLatLong->save();
                }

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Update area");

                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_UPDATE_MESSAGE'));
            }

            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
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
            // Delete
            $data = Area::find($id);

            // Delete also the location lat longt
            $getLoc = Location::whereIn('area_id', [$id]);
            if ($getLoc->get()->count() > 0) {
                $getLoc->delete();
            }

            // Delete area
            $data->delete();

            // Save log
            $controller = new SaveActivityLogController();
            $controller->saveLog($data->toArray(), "Delete area");

            //delete success
            return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_DELETE_MESSAGE'));
        } catch (Exception $e) {
            // delete failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_MESSAGE'));
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route($this->getRoute())->with('error', Config::get('const.ERROR_FOREIGN_KEY'));
        }
    }

    /**
     * Show all data polygon.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function showAllDataLocation($id)
    {
       
        return response()->json(Location::where('area_id', $id)->get());
    }

    /**
     * Save the location.
     *
     * @param Request $request
     * @return void
     */
    public function storeLocation(Request $request)
    {
        $new = $request->all();
        Location::create($new);
    }

    /**
     * Delete all data on locations table.
     *
     * @param Request $request
     * @return void
     */
    public function deleteLocationTable(Request $request)
    {
        $getLoc = Location::whereIn('area_id', [$request->area_id]);

        if ($getLoc->get()->count() > 0) {
            $getLoc->delete();
        }
    }
}
