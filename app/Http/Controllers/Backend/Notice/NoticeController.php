<?php

namespace App\Http\Controllers\Backend\Notice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;
use Auth;
use Config;

class NoticeController extends Controller
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
           
            'title'=>['title'=>'Title'],
            'created_at' => ['title' => 'Date & Time'],
            'message'=>['title'=>'Message'],
            'created_by'=>['title'=>'Created By'],
            'status' =>['title'=>'Status'],
            'action' => ['orderable' => false, 'searchable' => false],
            
        ];
 
        if ($datatables->getRequest()->ajax()) {
            $query = Notice::orderBy('id','desc')->get();

            return $datatables->of($query)
                ->addColumn('title', function (Notice $data) {
                    return $data->title ?? 'NA';
                })
                ->addColumn('created_at', function (Notice $data) {
                    return date('d-m-Y h:i:s',strtotime($data->created_at)) ?? 'NA';
                })

                ->addColumn('message', function (Notice $data) {
                    $message = '<div style="display: flex;">';
                    // $data->message
                    $message.='<div style="padding-right:3px;"><h4" title="'.$data->message.'">'.\Illuminate\Support\Str::limit($data->message,10,'...').'</h4></div>';
                    return $message ?? 'NA';
                })

                ->addColumn('created_by', function (Notice $data) {
                    return $data->created_by ?? 'NA';
                })
                ->addColumn('status', function (Notice $data) {
                    return $data->status==1 ? "Active" : "Deactive";
                })
                ->addColumn('action', function (Notice $data) {
                    // $routeEdit = route($this->getRoute() . '.edit', $data->id);
                    $routeActive = route($this->getRoute() . '.activeUser', $data->id);
                    $routeDeactive = route($this->getRoute() . '.deactiveUser', $data->id);
                    
                    // Check is administrator
                    if (Auth::user()->hasRole('administrator')) {
                        $button = '<div style="display: flex;">';
                        // $button .= '<div style="padding-right:3px;"><a href="'.$routeEdit.'"><button class="btn btn-primary btn-sm" title="click to edit"><i class="fa fa-edit"></i></button></a></div>';
                        
                        if($data->status == 1){
                            $button .= '<div><a href="'.$routeDeactive.'" class="confirm-button2" style="padding-right:5px;"><button class="btn btn-primary btn-sm" title="click to deactive"><i class="fa fa-ban" aria-hidden="true"></i></button></a></div>';
                        }
                       //activate button
                        if($data->status == 0){
                            $button .= '<div><a href="'.$routeActive.'" class="confirm-button3" style="padding-right:5px;" ><button class="btn btn-primary  btn-sm" title="click to active"><i class="fa fa-user-slash"></i></button></a></div>';
                           
                        }    
                    }
                    return $button;
                })
                
                ->rawColumns(['title','created_at','message','created_by','action'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[0,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.notice.index', compact('html'));
    }

    /**
     * Fungtion show button for export or print.
     *
     * @param $columnsArrExPr
     * @return array[]
     */
    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Notice";
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
        $data = new Notice();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Add';

        return view('backend.notice.form', [
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
        return 'notice';
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
            $new['created_by']  = Auth::user()->name ?? Null;
            $createNew = Notice::create($new);
            if ($createNew) {
                $createNew->save();
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_NOTICE_MESSAGE'));
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
           
            'title'     => $type == 'create' ? 'required|max:255' : "",
            'message'   => $type == 'create' ? 'required|max:500' : '',  
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
        $data = Notice::find($id);
        
        $data->form_action = $this->getRoute() . '.update';
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Edit';

        return view('backend.notice.form', [
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
            $currentData = Notice::find($request->get('id'));
            if ($currentData) {
                $this->validator($new, 'update')->validate();
                $new['created_by']  =Auth::user()->name ?? Null;
                $currentData->update($new);
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_UPDATE_NOTICE_MESSAGE'));
            }
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }


    public function delete($id)
    {
        try {
            if (Auth::user()->id != $id) {
                // delete
                $user = Notice::find($id);
                // Delete the data DB
                $user->delete();

                //delete success
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_DELETE_NOTICE_MESSAGE'));
            }
            // delete failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_SELF_MESSAGE'));
        } catch (Exception $e) {
            // delete failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_MESSAGE'));
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route($this->getRoute())->with('error', Config::get('const.ERROR_FOREIGN_KEY'));
        }
    }

    /**
     * update status to active start
     * */
    public function activeUser($id){
        $data = Notice::find($id);
        $data->status = '1';
        $data->save();
       
        return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_NOTICE_ACTIVE_MESSAGE'));
    }
    /**
     * update status to active end
     * */

    /**
     * update status to deactive start
     * */
    public function deactiveUser($id){
        $data = Notice::find($id);
        $data->status = '0';
        $data->save();
      
        return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_NOTICE_DEACTIVE_MESSAGE'));
    }

}
