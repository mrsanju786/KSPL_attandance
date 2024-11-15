<?php

namespace App\Http\Controllers\Backend\Users;

use Auth;
use File;
use Config;
use App\Models\Area;
use App\Models\Role;
use App\Models\User;
use App\Models\AssignRole;
use App\Models\RsmTsm;
use App\Models\TsmEmp;
use App\Models\TsmArea;
use App\Models\UserLog;
use App\Models\Designation;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Holiday;

class UsersController extends Controller
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
    public function index(Datatables $datatables , Request $request)
    {
        // dd($request->status);
        $columns = [
            'id' => ['title' => 'Sr.No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'emp_id',
            'image',
            'name',
            'email',
            'role',
            'branch_id'=>['title'=>'Deployed Sole ID'],
            'branch'=>['title'=>'Deployed Branch'],
            'mapped_sole_id'=>['title'=>'Mapped Sole ID'],
            'tsm'=>['title'=>AssignRole::where('role_id',5)->first()->display_name ?? 'TSM'],
            'rsm'=>['title'=>AssignRole::where('role_id',6)->first()->display_name ?? 'RSM'],
            'action' => ['orderable' => false, 'searchable' => false],
            'status',
            'created_at',
            'updated_at',
            'deactivated_by',
            'deactivated_at'
        ];
        if(isset($request->status)) {
            $cnt_query = User::all()->whereNotIn('role',[1,2,9])->where('status', $request->status)->count();
        }else{
            $cnt_query = User::all()->whereNotIn('role',[1,2,9])->count();
        }
        $userCount = $cnt_query;
        if ($datatables->getRequest()->ajax()) {
            $query = User::where('status',1)->whereNotIn('role',[1,2,9])->get();
            if(isset($request->status)) {
                $query = $query->where('status', $request->status);
            }
            
            return $datatables->of($query)
                ->addColumn('emp_id', function (User $user) {
                    return $user->emp_id;
                })
                ->addColumn('image', function (User $data) {
                    $getAssetFolder = asset('uploads/' . $data->image);
                    return '<img src="'.$getAssetFolder.'" width="30px" class="img-circle elevation-2">';
                })

                ->addColumn('mapped_sole_id', function (User $data) {
                        $soleId = Null;
                        $VALUE =Null;
                        $buttons = '<div style="display: flex;">';
                        $area = TsmArea::where('tsm_id',$data->id)->where('area_id','!=',$data->area_id)->pluck('area_id');
                        if(!empty($area)){
                            
                            $soleId  = Area::whereIn('id',$area)->pluck('name'); 
                            $ARRAY=json_decode($soleId,true);
                            ;
                            $VALUE=implode(',',$ARRAY);
                            // return  $VALUE;
                            // $buttons.='<div style="padding-right:3px;"><button class="btn btn-primary btn-sm" title="'.$VALUE.'"><i class="fa fa-users" aria-hidden="true"></i></button></div>';
                        }
                        if($data->role ==5){
                           
                            $buttons.='<div style="padding-right:3px;"><button class="btn btn-primary btn-sm" title="'.$VALUE.'"><i class="fa fa-users" aria-hidden="true"></i></button></div>';
                            
                        }elseif($data->role ==6){
                           
                            $buttons.='<div style="padding-right:3px;"><button class="btn btn-primary btn-sm" title="'.$VALUE.'"><i class="fa fa-users" aria-hidden="true"></i></button></div>';
                        
                       }
                        else{
                           
                            $buttons.='<div style="padding-right:3px;"><button class="btn btn-primary btn-sm" ><i class="fa fa-users" aria-hidden="true"></i></button></div>';
                            
                        }
                        
                        return $buttons;
                //     return '<div style="padding-right:3px;" ><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                //     <i class="fa fa-users" aria-hidden="true"></i>
                //   </button></div>';
                })
                ->addColumn('action', function (User $data) {
                    $routeEdit = route($this->getRoute() . '.edit', $data->id);
                    $routeDelete = route($this->getRoute() . '.delete', $data->id);
                    $routeActive = route($this->getRoute() . '.activeUser', $data->id);
                    $routeDeactive = route($this->getRoute() . '.deactiveUser', $data->id);
                    $routeLogoutUser = route($this->getRoute() . '.LogoutUser', $data->id);
                    $routeAttendance = route($this->getRoute() . '.routeAttendance', $data->id);

                    // Check is administrator
                    if (Auth::user()->hasRole('administrator')) {
                        $button = '<div style="display: flex;">';
                        $button .= '<div style="padding-right:3px;"><a href="'.$routeEdit.'"><button class="btn btn-primary btn-sm" title="click to edit"><i class="fa fa-edit"></i></button></a></div>';
                        // $button .= '<a href="'.$routeDelete.'" class="delete-button"><button class="btn btn-danger"><i class="fa fa-trash"></i></button></a>';
                        if($data->is_login == 1){
                        $button .= '<div style="padding-right:3px;"><a href="'.$routeLogoutUser.'"><button class="btn btn-primary btn-sm" title="click to logout"><i class="fas fa-sign-out-alt"></i></button></a></div>';
                        }
                        if($data->status == 1){
                            $button .= '<div><a href="'.$routeDeactive.'" class="confirm-button" style="padding-right:5px;"><button class="btn btn-primary btn-sm" title="click to deactive"><i class="fa fa-ban" aria-hidden="true"></i></button></a></div>';
                        }
                       //activate button
                        if($data->status == 0){
                            $button .= '<div><a href="'.$routeAttendance.'" class="confirm-button1" style="padding-right:5px;" ><button class="btn btn-primary  btn-sm" title="click to active"><i class="fa fa-user-slash"></i></button></a></div>';
                            // $button .= '<div style="padding-right:3px;"><a href="'.$routeAttendance.'"><button class="btn btn-primary btn-sm" title="click to attendance"><i class="fas fa-check-circle"></i></button></a></div>';
                        }
                        
                    } else {
                        $button = '<a href="#"><button class="btn btn-primary disabled"><i class="fa fa-edit"></i></button></a> ';
                        $button .= '<a href="#"><button class="btn btn-danger disabled"><i class="fa fa-trash"></i></button></a>';
                    }
                    return $button;
                })
                ->addColumn('role', function (User $user) {
                    return (!empty(AssignRole::where('role_id', $user->role)->where(['company_id' => Auth::user()->company_id])->first()->display_name)) ? AssignRole::where('role_id', $user->role)->where(['company_id' => Auth::user()->company_id])->first()->display_name : Role::where('id', $user->role)->first()->display_name;
                })
                ->addColumn('branch_id', function (User $user) {
                    $branch = Area::where('id', $user->area_id)->first();
                    if($branch){
                        return $branch->name;
                    }else{
                        return 'NA';
                    }
                })
                ->addColumn('branch', function (User $user) {
                    $branch = Area::where('id', $user->area_id)->first();
                    if($branch){
                        return $branch->address;
                    }else{
                        return 'NA';
                    }
                })
                ->addColumn('tsm', function (User $user) {  
                        if(TsmEmp::where('emp_id', $user->id)->exists()){
                            $val = TsmEmp::where('emp_id', $user->id)->first();
                            $t_user = User::where('id', $val->tsm_id)->where('role',5)->where('status',1)->first();
                            if(!empty($t_user)){
                               
                                return $t_user->name;
                            }else{
                                return 'NA';
                            }
                            
                        }else{
                            return 'NA';
                        }
                    
                    
                })
                ->addColumn('rsm', function (User $user) {
                    // if(TsmEmp::where('emp_id', $user->id)->exists()){
                    //     $val = TsmEmp::where('emp_id', $user->id)->first();
                    //     if(RsmTsm::where('tsm_id',$val->tsm_id)->exists()){
                    //         $rsmInfo = RsmTsm::where('tsm_id',$val->tsm_id)->first();
                    //         $rsm_name = User::where('id',$rsmInfo->rsm_id)->first();
                    //         if($rsm_name){
                    //             return $rsm_name->name;
                    //         }else{
                    //             return 'NA';   
                    //         }
                    //         return $rsm_name->name;
                    //     }else{
                    //         return 'NA';
                    //     }
                        
                    // }else{
                    //     return 'NA';
                    // }
                    $val = TsmEmp::where('emp_id', $user->id)->first();
                    if(!empty($val)){
                        $rsmInfo = RsmTsm::where('tsm_id',$val->tsm_id)->first();
                        if(!empty( $rsmInfo )){
                            $rsm_name = User::where('id',$rsmInfo->rsm_id)->where('role',6)->where('status',1)->first();
                            if($rsm_name){
                                return $rsm_name->name;
                            }else{
                                return 'NA';
                            }
                        }
                        
                    }

                    
                    //tsm rsm 
                    $rsmInfo1 = RsmTsm::where('tsm_id',$user->id)->first();
                    if(!empty( $rsmInfo1 )){
                        $rsm_name1 = User::where('id',$rsmInfo1->rsm_id)->where('role',6)->where('status',1)->first();
                        if($rsm_name1){
                            return $rsm_name1->name;
                        }else{
                            return 'NA';
                        }
                    }

                    
                     //obst rsm name
                     $rsmInfo2 = TsmEmp::where('emp_id',$user->id)->first();
                     if(!empty( $rsmInfo2 )){
                         $rsm_name2 = User::where('id',$rsmInfo2->tsm_id)->where('role',6)->where('status',1)->first();
                         if($rsm_name2){
                             return $rsm_name2->name;
                         }else{
                             return 'NA';
                         }
                     }
                })
                ->addColumn('status', function (User $user) {
                    return $user->status==0?'Deactive':'Active';
                })
                ->addColumn('deactivated_by', function (User $user) {
                    if(User::where('id', $user->deactivated_by)->exists()) {
                        return User::where('id', $user->deactivated_by)->first()->name;
                    } else {
                        return '-';
                    }
                })
                ->addColumn('deactivated_at', function (User $user) {
                    return $user->deactivated_at;
                })
                ->rawColumns(['mapped_sole_id','action', 'image', 'intro'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'order' => [[1,'desc'],[6,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);
            
        return view('backend.users.index', compact('html','userCount'));
    }

    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "Users";
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

    public function LogoutUser($id){

        $user_id = $id;
        $user = User::where('id',$user_id)->first();
        $user->is_login = 0;
        $user->save();

        $logdate = Carbon::now();
        $logout_detail = UserLog::where('user_id', $user_id)->orderBy('id', 'desc')->first();
        if($logout_detail){
            $logout_detail->logout_date = $logdate->toDateString();
            $logout_detail->logout_time = $logdate->toTimeString();
            $logout_detail->logout_by = Auth::user()->id;
            $logout_detail->save();
        }
        return redirect()->back();
    }


    // tsm rsm based area list
    public function tsm_rsm_area_list(Request $request){
       $tsm_rsm=User::find($request->id);
       $areas=$tsm_rsm->areas;
       return json_encode($areas);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add()
    {
        $data = new User();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Add';

        $assignRoles = AssignRole::select(['role_id as id', 'display_name','last_updated_by', 'created_at', 'updated_at'])->where(['company_id' => Auth::user()->company_id])
        ->orderBy('id','asc')
        ->pluck('id','display_name');

        if (Auth::user()->hasRole('administrator')) {
            //dd($assignRoles->values()->toArray());
            $roles = Role::whereNotIn('id', [10])
                ->whereNotIn('id', $assignRoles->values()->toArray())
                ->orderBy('id','asc')
                ->pluck('id','display_name');
        }else{
            $roles = Role::whereNotIn('id', [10,1])
                ->whereNotIn('id', $assignRoles->values()->toArray())
                ->orderBy('id','asc')
                ->pluck('id','display_name');
        }
        
            $mergedRoles = $assignRoles->merge($roles);
            // Swap the keys and values in the merged roles array
            $mergedRoles = $mergedRoles->sortBy(function ($value) {
                return $value;
            });
            // Swap the keys and values in the merged roles array
            $mergedRoles = $mergedRoles->flip();

        
    
        if (Auth::user()->hasRole('administrator')) {
            return view('backend.users.form', [
                'data' => $data,
                'role' => $mergedRoles->toArray(),
                'designation'=>Designation::where('status',1)->orderBy('id')->pluck('name', 'id'),
                'area_id'=>Area::orderBy('id')->pluck('address', 'id'),
                'rsm'=>User::where('role',6)->orderBy('id')->pluck('name', 'id'),
                'tsm_rsm'=>User::whereIn('role',[5,6])->orderBy('id')->pluck('name', 'id'),
                 'area'=>Area::orderBy('id')->get(),
                 'tsm_area' => TsmArea::where('tsm_id',$data->id)->pluck('area_id')
            ]);
        }
        //$mergedRoles = Role::whereNotIn('id', [10])->get()->toArray();
            return view('backend.users.form', [
                'data' => $data,
                'role' => $mergedRoles->toArray(),
                'designation'=>Designation::where('status',1)->orderBy('id')->pluck('name', 'id'),
                'area_id'=>Area::orderBy('id')->pluck('address', 'id'),
                'rsm'=>User::where('role',6)->orderBy('id')->pluck('name', 'id'),
                'tsm_rsm'=>User::whereIn('role',[5,6])->orderBy('id')->pluck('name', 'id'),
                 'area'=>Area::orderBy('id')->get(),
                 'tsm_area' => TsmArea::where('tsm_id',$data->id)->pluck('area_id')
            ]);
        }

    /**
     * Get named route depends on which user is logged in
     *
     * @return String
     */
    private function getRoute()
    {
        return 'users';
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
        if(User::where('emp_id', $new['emp_id'])->exists()){
            return redirect(url()->previous())
                    ->withErrors('Employee Id already exist.')
                    ->withInput();
        }

        if(User::where('mobile_number', $new['mobile_number'])->exists()){
            return redirect(url()->previous())
                    ->withErrors('Mobile Number already exist.')
                    ->withInput();
        }
        $this->validator($new, 'create')->validate();
        try {
            $new['password'] = bcrypt($new['password']);

            // DB::beginTransaction();
            $createNew = User::create($new);
            if ($createNew) {

                // Attach role
                $createNew->roles()->attach($new['role']);

                // upload image
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    // image file name example: [news_id]_image.jpg
                    $imageName = $createNew->id . "_image." . $file->getClientOriginalExtension();
                    // save image to the path
                    $file->move(Config::get('const.UPLOAD_PATH'), $imageName);
                    $createNew->{'image'} = $imageName;
                } else {
                    $createNew->{'image'} = 'default-user.png';
                }


                // Save user
                $createNew->save();

                // multiple area
                if($new['role']==5 || $new['role']==6){
                    if(!empty($new['area_id_multiple'])){
                        foreach ($new['area_id_multiple'] as $key => $value_id) {
                            $value = Area::where('id', $value_id)->first();
                            $tsm_area=new TsmArea;
                            $tsm_area->tsm_id=$createNew->id;
                            $tsm_area->area_id=$value->id;
                            $tsm_area->save();
                        }
                    }
                }

                if($new['role']==3){
                    if($new['tsm_rsm']){
                        $tsm_emp=new TsmEmp;
                        $tsm_emp->tsm_id=$new['tsm_rsm'];
                        $tsm_emp->emp_id=$createNew->id;
                        $tsm_emp->save();
                    }
                }

                if($new['role']==5){
                    if($new['rsm']){
                        $rsm_tsm=new RsmTsm;
                        $rsm_tsm->rsm_id=$new['rsm'];
                        $rsm_tsm->tsm_id=$createNew->id;
                        $rsm_tsm->save();
                    }
                }
                // DB::commit();

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Create new user");

                // Create is successful, back to list
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
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
        // Determine if password validation is required depending on the calling
        return Validator::make($data, [
            // Add unique validation to prevent for duplicate email while forcing unique rule to ignore a given ID
            'email'         => $type == 'create' ? 'email|required|string|max:255|unique:users' : 'required|string|max:255|unique:users,email,' . $data['id'],
            // (update: not required, create: required)
            'emp_id'        => $type == 'create' ? 'required|max:255' : '',
            'name'          => $type == 'create' ? 'required|max:255' : '',
            'mobile_number' => $type == 'create' ? 'required|max:255' : '',
            'password'      => $type == 'create' ? 'required|string|min:6|max:255' : '',
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
        $data = User::find($id);
        $data['area_id_multiple']=$data->areas;
        
       
        
        if($data->role==5){
            $res_tsm_rsm = RsmTsm::where('tsm_id',$id)->first();
            isset($res_tsm_rsm)?$data['rsm']=$res_tsm_rsm->rsm_id:'';
        }
        if($data->role==3){
            isset($data->tsmEmp->tsm_id)?$data['tsm_rsm']=$data->tsmEmp->tsm_id:'';
        }
        $data->form_action = $this->getRoute() . '.update';
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Update';

        $assignRoles = AssignRole::select(['role_id as id', 'display_name','last_updated_by', 'created_at', 'updated_at'])->where(['company_id' => Auth::user()->company_id])
        ->orderBy('id','asc')
        ->pluck('id','display_name');

        if (Auth::user()->hasRole('administrator')) {
            //dd($assignRoles->values()->toArray());
            $roles = Role::whereNotIn('id', [10])
                ->whereNotIn('id', $assignRoles->values()->toArray())
                ->orderBy('id','asc')
                ->pluck('id','display_name');
        }else{
            $roles = Role::whereNotIn('id', [10,1])
                ->whereNotIn('id', $assignRoles->values()->toArray())
                ->orderBy('id','asc')
                ->pluck('id','display_name');
        }
        
            $mergedRoles = $assignRoles->merge($roles);
            // Swap the keys and values in the merged roles array
            $mergedRoles = $mergedRoles->sortBy(function ($value) {
                return $value;
            });
            // Swap the keys and values in the merged roles array
            $mergedRoles = $mergedRoles->flip();

        if (Auth::user()->hasRole('administrator')) {
            return view('backend.users.form', [
                'data' => $data,
                'role' => $mergedRoles->toArray(),
                'designation'=>Designation::orderBy('id')->pluck('name', 'id'),
                'area_id'=>Area::orderBy('id')->pluck('address', 'id'),
                'rsm'=>User::where('role',6)->orderBy('id')->pluck('name', 'id'),
                'tsm_rsm'=>User::whereIn('role',[5,6])->orderBy('id')->pluck('name', 'id'),
                'area'=>Area::orderBy('id')->get(),
                'tsm_area' => TsmArea::where('tsm_id',$id)->pluck('area_id')

            ]);
        }

        return view('backend.users.form', [
            'data' => $data,
            'role' => $mergedRoles->toArray(),
            'designation'=>Designation::orderBy('id')->pluck('name', 'id'),
            'area_id'=>Area::orderBy('id')->pluck('address', 'id'),
            'rsm'=>User::where('role',6)->orderBy('id')->pluck('name', 'id'),
            'tsm_rsm'=>User::whereIn('role',[5,6])->orderBy('id')->pluck('name', 'id'),
            'area'=>Area::orderBy('id')->get(),
            'tsm_area' => TsmArea::where('tsm_id',$id)->pluck('area_id')

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
        $u_ids = User::whereNotIn('id',[$request->get('id')])->pluck('emp_id')->toArray();
                
        if(in_array($request->get('emp_id'), $u_ids)){
            return redirect(url()->previous())
                    ->withErrors('Employee Id already exist.')
                    ->withInput();
        }
        try {
            $currentData = User::find($request->get('id'));
            if ($currentData) {
                $this->validator($new, 'update')->validate();

                if (!$new['password']) {
                    $new['password'] = $currentData['password'];
                } else {
                    $new['password'] = bcrypt($new['password']);
                }

                if ($currentData->role != $new['role']) {
                    $currentData->roles()->sync($new['role']);
                }

                // check delete flag: [name ex: image_delete]
                if ($request->get('image_delete') != null) {
                    $new['image'] = null; // filename for db

                    if ($currentData->{'image'} != 'default-user.png') {
                        // @unlink(Config::get('const.UPLOAD_PATH') . $currentData['image']);
                        unlink(Config::get('const.UPLOAD_PATH') . $currentData['image']);
                    }
                }

                // if new image is being uploaded
                // upload image

                if ($request->file('image')) {
                    // log::debug("----------condition True----------");
                    $file = $request->file('image');
              
                    $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();


                    $new['image'] = $imageName;
                    // save image to the path
                    $file->move(Config::get('const.UPLOAD_PATH'), $imageName);
                } else {
                    // unset($new['image']);

                    // $new['image'] = 'default-user.png';
                }


                if($currentData['role']==5||$currentData['role']==6){
                    $oldAreaMultiple=TsmArea::where('tsm_id', $request->id)->pluck('area_id')->toArray();
                    if(!empty($new['area_id_multiple'])){
                       $newAreaMultiple=$new['area_id_multiple'];
                    }else{
                        $newAreaMultiple=[];
                    }

                    $newAreasToAdd = array_diff($newAreaMultiple, $oldAreaMultiple);
                    $oldAreasToRemove = array_diff($oldAreaMultiple, $newAreaMultiple);

                    // dd($newAreasToAdd);
                    // dd($oldAreasToRemove);

                    // Delete areas which are not in list
                    if ($oldAreasToRemove) {
                        TsmArea::whereIn('area_id', $oldAreasToRemove)->where('tsm_id', $request->id)->delete();
                    }
                    // Add areas which are in request
                    if ($newAreasToAdd) {
                        foreach ($newAreasToAdd as $key => $value_id) {
                            $value = Area::where('id', $value_id)->first();
                            $tsm_area=new TsmArea;
                            $tsm_area->tsm_id=$request->id;
                            $tsm_area->area_id=$value->id;
                            $tsm_area->save();

                        }
                    }
                }

                if($currentData['role']==3){
                    if($new['tsm_rsm']){
                        // $tsm_emp=TsmEmp::where(['tsm_id',$currentData['tsm_rsm']],['emp_id',$currentData->id])->first(); previous query
                        $tsm_emp=TsmEmp::where('emp_id',$currentData->id)->first();
                        if(isset($tsm_emp)){
                            $tsm_emp->tsm_id=$new['tsm_rsm'];
                            $tsm_emp->emp_id=$currentData->id;
                            $tsm_emp->save();
                        }else{
                            $tsm_emp= new TsmEmp();
                            $tsm_emp->tsm_id=$new['tsm_rsm'];
                            $tsm_emp->emp_id=$currentData->id;
                            $tsm_emp->save();
                        }
                        
                    }
                }

                if($currentData['role']==5){
                    if($new['rsm']){
                        // $rsm_tsm=TsmRsm::where(['rsm_id',$currentData['rsm']],['tsm_id',$currentData->id])->first(); previous query
                        // new code start
                        $rsm_tsm=RsmTsm::where('tsm_id',$currentData->id)->first();
                        if(isset($rsm_tsm)){
                            $rsm_tsm->rsm_id = $new['rsm'];
                            // $rsm_tsm->tsm_id=$createNew->id;
                            $rsm_tsm->tsm_id=$currentData->id;
                            $rsm_tsm->save();
                        }else{
                            $rsm_tsm = new RsmTsm();
                            $rsm_tsm->rsm_id = $new['rsm'];
                            $rsm_tsm->tsm_id=$currentData->id;
                            $rsm_tsm->save();
                        }
                        
                    }
                }
                // dd($new);
                // Update
                $currentData->update($new);

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Update user");

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
            if (Auth::user()->id != $id) {

                // delete
                $user = User::find($id);
                $user->detachRole($id);

                // Delete the image
                if ($user->{'image'} != 'default-user.png') {
                    @unlink(Config::get('const.UPLOAD_PATH') . $user['image']);
                }

                // Delete the data DB
                $user->delete();

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($user->toArray(), "Delete user");

                //delete success
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_DELETE_MESSAGE'));
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
        $data = User::find($id);
        $data->status = '1';
        $data->save();

        // Save log
        $controller = new SaveActivityLogController();
        $controller->saveLog($data->toArray(), "Active user");

        //user status success
        return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_USER_ACTIVE_MESSAGE'));
    }
    /**
     * update status to active end
     * */

    /**
     * update status to deactive start
     * */
    public function deactiveUser($id){
        $tDate = Carbon::now()->toDateTimeString();
        $data = User::find($id);
        $data->deactivated_by = Auth::user()->id;
        $data->deactivated_at = $tDate;
        $data->status = '0';
        $data->save();

        // Save log
        $controller = new SaveActivityLogController();
        $controller->saveLog($data->toArray(), "Active user");

        //user status success
        return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_USER_DEACTIVE_MESSAGE'));
    }
    /**
     * update status to deactive end
     * */

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function import()
    {
        $data = new User();
        $data->form_action = $this->getRoute() . '.importData';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Import';

        return view('backend.users.import', [
            'data' => $data,
        ]);
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

        $import_type=$request->import_type;

        // import type => 3-staff,2-tsm ,1-rsm ,4-BOA ,5-DST

    try {
             // import staff
        if($import_type==3){
            // If file extension is 'csv'
         if ($request->hasFile('import')) {
             $file = $request->file('import');

             // File Details
             $extension = $file->getClientOriginalExtension();

             // If file extension is 'csv'
             if ($extension == 'csv') {
                 $fp = fopen($file, 'rb');

                 $header = fgetcsv($fp, 0, ',');
                 $countheader = count($header);

                 // Check is csv file is correct format
                 if ($countheader==10 && in_array('emp_id', $header, true) && in_array('name', $header, true)
                 && in_array('branch_id', $header, true) && in_array('email', $header, true) && in_array('password', $header, true) && in_array('designation', $header, true) && in_array('mobile_number', $header, true) && in_array('tsm_emp_id', $header, true) && in_array('blood_group', $header, true)  && in_array('emergency_contact', $header, true)) {
                     // Loop the row data csv

                    //  DB::beginTransaction();
                     while (($csvData = fgetcsv($fp)) !== false) {

                         $csvData = array_map('utf8_encode', $csvData);

                         // Row column length
                         $dataLen = count($csvData);

                         // Skip row if length != 8
                        //  if (!($dataLen == 8)) {
                        //      continue;
                        //  }

                         // Assign value to variables
                         $emp_id = $csvData[0];
                         $name = $csvData[1];
                         $branches_id = $csvData[2];
                         $email=trim($csvData[3]);
                         $role = 3;

                         // Insert data to users table
                         // Check if any duplicate email
                         if ($this->checkDuplicate($email, 'email')) {
                             $errorArr[] = $email;
                             $str = implode(", ", $errorArr);
                             $errorMessage = '-Some data email already exists ( ' . $str . ' )';
                             continue;
                         }

                         $password = trim($csvData[4]);
                         $hashed = bcrypt($password);

                         $designation_value=trim($csvData[5]);

                         $designations=Designation::where(strtolower('name'),'like','%'.strtolower($designation_value).'%')->first();

                         $designation=$designations->id;

                         $mobile_number=$csvData[6];

                         $tsm_emp_id=$csvData[7];
                         $blood_group=$csvData[8];
                         $emergency_contact=$csvData[9];


                         $home_branch = Area::where('name', $branches_id)->first();
                         if (!$home_branch) {
                            $home_branch = new Area();
                            $home_branch->name = $branches_id;
                            $home_branch->address = 'NA';
                            $home_branch->company_id = 1;
                            $home_branch->save();
                        }
                         $data = array(
                             'name'=>$name,
                             'email' => $email,
                             'emp_id'=>$emp_id,
                             'area_id'=>$home_branch->id,
                             'role' => $role,
                             'password' => $hashed,
                             'designation'=>$designation,
                             'mobile_number'=>$mobile_number,
                             'blood_group'=>$blood_group,
                             'emergency_contact'=>$emergency_contact,
                             'image' => 'default-user.png',
                         );

                         // create the user
                         $createNew = User::create($data);

                        //  Attach role
                         $createNew->roles()->attach($role);

                        //  Save user
                         $createNew->save();


                         $tsm_id=User::where('emp_id',$tsm_emp_id)->first();

                         $tsm_emp=new TsmEmp;
                         $tsm_emp->tsm_id=$tsm_id->id;
                         $tsm_emp->emp_id=$createNew->id;
                         $tsm_emp->save();


                     }
                    //  DB::commit();


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

        }

         //import DST
         if($import_type==5){
            // If file extension is 'csv'
         if ($request->hasFile('import')) {
             $file = $request->file('import');

             // File Details
             $extension = $file->getClientOriginalExtension();

             // If file extension is 'csv'
             if ($extension == 'csv') {
                 $fp = fopen($file, 'rb');

                 $header = fgetcsv($fp, 0, ',');
                 $countheader = count($header);

                 // Check is csv file is correct format
                 if ($countheader==9 && in_array('emp_id', $header, true) && in_array('name', $header, true)
                 && in_array('branch_id', $header, true) && in_array('email', $header, true) && in_array('password', $header, true) && in_array('designation', $header, true) && in_array('mobile_number', $header, true)
                 && in_array('blood_group', $header, true) && in_array('emergency_contact', $header, true)) {
                     // Loop the row data csv

                    //  DB::beginTransaction();
                     while (($csvData = fgetcsv($fp)) !== false) {

                         $csvData = array_map('utf8_encode', $csvData);

                         // Row column length
                         $dataLen = count($csvData);

                        //  // Skip row if length != 8
                        //  if (!($dataLen == 7)) {
                        //      continue;
                        //  }

                         // Assign value to variables
                         $emp_id = $csvData[0];
                         $name = $csvData[1];
                         $branches_id = $csvData[2];
                         $email=trim($csvData[3]);
                         $role = 8;

                         // Insert data to users table
                         // Check if any duplicate email
                         if ($this->checkDuplicate($email, 'email')) {
                             $errorArr[] = $email;
                             $str = implode(", ", $errorArr);
                             $errorMessage = '-Some data email already exists ( ' . $str . ' )';
                             continue;
                         }

                         $password = trim($csvData[4]);
                         $hashed = bcrypt($password);

                         $designation_value=trim($csvData[5]);

                         $designations=Designation::where(strtolower('name'),'like','%'.strtolower($designation_value).'%')->first();

                         $designation=$designations->id;

                         $mobile_number=$csvData[6];
                         $blood_group=$csvData[7];
                         $emergency_contact=$csvData[8];


                         $home_branch = Area::where('name', $branches_id)->first();
                         if (!$home_branch) {
                            $home_branch = new Area();
                            $home_branch->name = $branches_id;
                            $home_branch->address = 'NA';
                            $home_branch->company_id = 1;
                            $home_branch->save();
                        }
                         $data = array(
                             'name'=>$name,
                             'email' => $email,
                             'emp_id'=>$emp_id,
                             'area_id'=>$home_branch->id,
                             'role' => $role,
                             'password' => $hashed,
                             'designation'=>$designation,
                             'mobile_number'=>$mobile_number,
                             'blood_group'=>$blood_group,
                             'emergency_contact'=>$emergency_contact,
                             'image' => 'default-user.png',
                         );

                        //  dd($data);

                         // create the user
                         $createNew = User::create($data);

                        //  Attach role
                         $createNew->roles()->attach($role);

                        //  Save user
                         $createNew->save();

                     }
                    //  DB::commit();


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

        }

        //import BOA
        if($import_type==4){
            // If file extension is 'csv'

         if ($request->hasFile('import')) {
             $file = $request->file('import');

             // File Details
             $extension = $file->getClientOriginalExtension();

             // If file extension is 'csv'
             if ($extension == 'csv') {
                 $fp = fopen($file, 'rb');

                 $header = fgetcsv($fp, 0, ',');
                 $countheader = count($header);

                 // Check is csv file is correct format
                 if ($countheader==9 && in_array('emp_id', $header, true) && in_array('name', $header, true)
                 && in_array('branch_id', $header, true) && in_array('email', $header, true) && in_array('password', $header, true) && in_array('designation', $header, true) && in_array('mobile_number', $header, true)  && in_array('blood_group', $header, true) && in_array('emergency_contact', $header, true)) {
                     // Loop the row data csv

                    //  DB::beginTransaction();
                     while (($csvData = fgetcsv($fp)) !== false) {

                         $csvData = array_map('utf8_encode', $csvData);

                         // Row column length
                         $dataLen = count($csvData);

                         // Skip row if length != 8
                        //  if (!($dataLen == 7)) {
                        //      continue;
                        //  }

                         // Assign value to variables
                         $emp_id = $csvData[0];
                         $name = $csvData[1];
                         $branches_id = $csvData[2];
                         $email=trim($csvData[3]);
                         $role = 7;

                         // Insert data to users table
                         // Check if any duplicate email
                         if ($this->checkDuplicate($email, 'email')) {
                             $errorArr[] = $email;
                             $str = implode(", ", $errorArr);
                             $errorMessage = '-Some data email already exists ( ' . $str . ' )';
                             continue;
                         }

                         $password = trim($csvData[4]);
                         $hashed = bcrypt($password);

                         $designation_value=trim($csvData[5]);

                         $designations=Designation::where(strtolower('name'),'like','%'.strtolower($designation_value).'%')->first();

                         $designation=$designations->id;

                         $mobile_number=$csvData[6];
                         $blood_group=$csvData[7];
                         $emergency_contact=$csvData[8];


                         $home_branch = Area::where('name', $branches_id)->first();
                         if (!$home_branch) {
                            $home_branch = new Area();
                            $home_branch->name = $branches_id;
                            $home_branch->address = 'NA';
                            $home_branch->company_id = 1;
                            $home_branch->save();
                        }
                         $data = array(
                             'name'=>$name,
                             'email' => $email,
                             'emp_id'=>$emp_id,
                             'area_id'=>$home_branch->id,
                             'role' => $role,
                             'password' => $hashed,
                             'designation'=>$designation,
                             'mobile_number'=>$mobile_number,
                             'blood_group'=>$blood_group,
                             'emergency_contact'=>$emergency_contact,
                             'image' => 'default-user.png',
                         );

                         // create the user
                         $createNew = User::create($data);

                        //  Attach role
                         $createNew->roles()->attach($role);

                        //  Save user
                         $createNew->save();

                     }
                    //  DB::commit();


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

        }

        // import tsm

        if($import_type==2){
               // If file extension is 'csv'
            if ($request->hasFile('import')) {
                $file = $request->file('import');

                // File Details
                $extension = $file->getClientOriginalExtension();

                // If file extension is 'csv'
                if ($extension == 'csv') {
                    $fp = fopen($file, 'rb');

                    $header = fgetcsv($fp, 0, ',');
                    $countheader = count($header);


                    // Check is csv file is correct format
                    if ( $countheader==11 && in_array('emp_id', $header, true) && in_array('name', $header, true)
                    && in_array('home_branch_id', $header, true) && in_array('branches_id', $header, true) && in_array('email', $header, true) && in_array('password', $header, true) && in_array('designation', $header, true) && in_array('mobile_number', $header, true) && in_array('rsm_emp_id', $header, true) && in_array('blood_group', $header, true) && in_array('emergency_contact', $header, true) ) {
                        // Loop the row data csv

                        // DB::beginTransaction();
                        while (($csvData = fgetcsv($fp)) !== false) {

                            $csvData = array_map('utf8_encode', $csvData);

                            // Row column length
                            $dataLen = count($csvData);

                            // Skip row if length != 8
                            if (!($dataLen == 11)) {
                                continue;
                            }

                            // Assign value to variables
                            $emp_id = $csvData[0];
                            $name = $csvData[1];
                            $home_branch_id = $csvData[2];
                            $branches_id=$csvData[3];
                            $email=trim($csvData[4]);
                            $role = 5;

                            // Insert data to users table
                            // Check if any duplicate email
                            if ($this->checkDuplicate($email, 'email')) {
                                $errorArr[] = $email;
                                $str = implode(", ", $errorArr);
                                $errorMessage = '-Some data email already exists ( ' . $str . ' )';
                                continue;
                            }

                            $password = trim($csvData[5]);
                            $hashed = bcrypt($password);

                            $designation_value=trim($csvData[6]);


                            $designations=Designation::where(strtolower('name'),'like','%'.strtolower($designation_value).'%')->first();

                            $designation=$designations->id;

                            $mobile_number=$csvData[7];

                            $rsm_emp_id=$csvData[8];


                            $blood_group=$csvData[9];
                            $emergency_contact=$csvData[10];


                            $home_branch = Area::where('name', $home_branch_id)->first();
                            if (!$home_branch) {
                                $home_branch = new Area();
                                $home_branch->name = $home_branch_id;
                                $home_branch->address = 'NA';
                                $home_branch->company_id = 1;
                                $home_branch->save();
                            }
                            $data = array(
                                'name'=>$name,
                                'email' => $email,
                                'emp_id'=>$emp_id,
                                'area_id'=>$home_branch->id,
                                'role' => $role,
                                'password' => $hashed,
                                'designation'=>$designation,
                                'mobile_number'=>$mobile_number,
                                'blood_group'=>$blood_group,
                                'emergency_contact'=>$emergency_contact,
                                'image' => 'default-user.png',
                            );

                            // create the user
                            $createNew = User::create($data);

                            // Attach role
                            $createNew->roles()->attach($role);

                            // Save user
                            $createNew->save();

                            // rsm
                            $rsm_id=User::where('emp_id',$rsm_emp_id)->first();
                            // Log::debug($rsm_id);
                            $rsm_tsm=new RsmTsm;
                            $rsm_tsm->rsm_id=$rsm_id->id;
                            $rsm_tsm->tsm_id=$createNew->id;
                            $rsm_tsm->save();

                            // save branches comma seperated id
                            $branches_array=[];
                            $branches_array=explode(',',$branches_id);

                            foreach ($branches_array as $key => $value_id) {
                                $value = Area::where('name', $value_id)->first();
                                if (!$value) {
                                    $value = new Area();
                                    $value->name = $value_id;
                                    $value->address = 'NA';
                                    $value->company_id = 1;
                                    $value->save();
                                }
                                $tsm_area=new TsmArea;
                                $tsm_area->tsm_id=$createNew->id;
                                $tsm_area->area_id=$value->id;
                                $tsm_area->save();


                            }


                        }

                        // DB::commit();


                        if ($errorMessage == '') {
                            return redirect()->route($this->getRoute())->with('success', 'Imported was success!');
                        }
                        return redirect()->route($this->getRoute())->with('warning', 'Imported was success! <br><b>Note: We do not import this data  because</b><br>' . $errorMessage);
                    }
                    return redirect()->route($this->getRoute())->with('error', 'Import failed! You are using the wrong CSV format. Please use the CSV template to import your data.');
                }
                return redirect()->route($this->getRoute())->with('error', 'Please choose file with .CSV extension.');
            }

            return redirect()->route($this->getRoute())->with('error', 'Please select CSV file.');

        }



        // import rsm

        if($import_type==1){
            // If file extension is 'csv'
         if ($request->hasFile('import')) {
             $file = $request->file('import');

             // File Details
             $extension = $file->getClientOriginalExtension();

             // If file extension is 'csv'
             if ($extension == 'csv') {
                 $fp = fopen($file, 'rb');

                 $header = fgetcsv($fp, 0, ',');
                 $countheader = count($header);

                 // Check is csv file is correct format
                 if ( $countheader==10 && in_array('emp_id', $header, true) && in_array('name', $header, true)
                 && in_array('home_branch_id', $header, true) && in_array('branches_id', $header, true) && in_array('email', $header, true) && in_array('password', $header, true) && in_array('designation', $header, true) && in_array('mobile_number', $header, true)&& in_array('blood_group', $header, true) && in_array('emergency_contact', $header, true)  ) {
                     // Loop the row data csv

                    //  DB::beginTransaction();
                     while (($csvData = fgetcsv($fp)) !== false) {
                         $csvData = array_map('utf8_encode', $csvData);

                         // Row column length
                         $dataLen = count($csvData);

                         // Skip row if length != 8
                         if (!($dataLen == 10)) {
                             continue;
                         }

                         // Assign value to variables
                         $emp_id = $csvData[0];
                         $name = $csvData[1];
                         $home_branch_id = $csvData[2];
                         $branches_id=$csvData[3];
                         $email=trim($csvData[4]);
                         $role = 6;

                         // Insert data to users table
                         // Check if any duplicate email
                         if ($this->checkDuplicate($email, 'email')) {
                             $errorArr[] = $email;
                             $str = implode(", ", $errorArr);
                             $errorMessage = '-Some data email already exists ( ' . $str . ' )';
                             continue;
                         }

                         $password = trim($csvData[5]);
                         $hashed = bcrypt($password);

                         $designation_value=trim($csvData[6]);


                         $designations=Designation::where(strtolower('name'),'like','%'.strtolower($designation_value).'%')->first();

                         $designation=$designations->id;

                         // dd($designation);
                         $mobile_number=$csvData[7];
                         $blood_group=$csvData[8];
                         $emergency_contact=$csvData[9];

                         $home_branch = Area::where('name', $home_branch_id)->first();
                        if (!$home_branch) {
                            $home_branch = new Area();
                            $home_branch->name = $home_branch_id;
                            $home_branch->address = 'NA';
                            $home_branch->company_id = 1;
                            $home_branch->save();
                        }

                         $data = array(
                             'name'=>$name,
                             'email' => $email,
                             'emp_id'=>$emp_id,
                             'area_id'=>$home_branch->id,
                             'role' => $role,
                             'password' => $hashed,
                             'designation'=>$designation,
                             'mobile_number'=>$mobile_number,
                             'blood_group'=>$blood_group,
                             'emergency_contact'=>$emergency_contact,
                             'image' => 'default-user.png',
                         );

                         // create the user
                         $createNew = User::create($data);

                         // Attach role
                         $createNew->roles()->attach($role);

                         // Save user
                         $createNew->save();


                         // save branches comma seperated id
                         $branches_array=[];
                         $branches_array=explode(',',$branches_id);

                         foreach ($branches_array as $key => $value_id) {
                            $value = Area::where('name', $value_id)->first();
                            if (!$value) {
                                $value = new Area();
                                $value->name = $value_id;
                                $value->address = 'NA';
                                $value->company_id = 1;
                                $value->save();
                            }
                            $tsm_area=new TsmArea;
                            $tsm_area->tsm_id=$createNew->id;
                            $tsm_area->area_id=$value->id;
                            $tsm_area->save();
                         }

                     }
                    //  DB::commit();


                     if ($errorMessage == '') {
                         return redirect()->route($this->getRoute())->with('success', 'Imported was success!');
                     }
                     return redirect()->route($this->getRoute())->with('warning', 'Imported was success! <br><b>Note: We do not import this data  because</b><br>' . $errorMessage);
                 }
                 return redirect()->route($this->getRoute())->with('error', 'Import failed! You are using the wrong CSV format. Please use the CSV template to import your data.');
             }
             return redirect()->route($this->getRoute())->with('error', 'Please choose file with .CSV extension.');
         }
             return redirect()->route($this->getRoute())->with('error', 'Please select CSV file.');
        }
        //obst tsm insert
        // if($import_type==6){
        //     // If file extension is 'csv'
        //  if ($request->hasFile('import')) {
        //      $file = $request->file('import');

        //      // File Details
        //      $extension = $file->getClientOriginalExtension();

        //      // If file extension is 'csv'
        //      if ($extension == 'csv') {
        //          $fp = fopen($file, 'rb');

        //          $header = fgetcsv($fp, 0, ',');
        //          $countheader = count($header);

        //          // Check is csv file is correct format
        //          if ( $countheader==2 && in_array('emp_id', $header, true) && in_array('tsm_emp_id', $header, true)  ) {
        //              // Loop the row data csv

        //             //  DB::beginTransaction();
        //              while (($csvData = fgetcsv($fp)) !== false) {
        //                  $csvData = array_map('utf8_encode', $csvData);

        //                  // Row column length
        //                  $dataLen = count($csvData);

        //                  // Skip row if length != 8
        //                 //  if (!($dataLen == 10)) {
        //                 //      continue;
        //                 //  }

        //                  // Assign value to variables
        //                  $emp_id = $csvData[0];
        //                  $tsm_emp_id   = $csvData[1];
                         
        //                  //check obst
        //                  $emp = User::where('emp_id',$emp_id)->where('role',3)->where('status',1)->first();
        //                  if(!empty($emp)){
        //                     //check tsm 
        //                     $tsm = User::where('emp_id',$tsm_emp_id)->where('role',5)->where('status',1)->first();

        //                     $tsm_emp = TsmEmp::where('emp_id',$emp->id)->first();
        //                     if(!empty($tsm_emp)){
        //                         $tsm_emp->emp_id =$emp->id;
        //                         if(!empty($tsm->id)){
        //                             $tsm_emp->tsm_id =$tsm->id;
        //                         }    
        //                         $tsm_emp->save();
                                
                                
        //                     }else{

        //                         $newTsm = new TsmEmp;
        //                         $newTsm->emp_id =$emp->id;
        //                         if(!empty($tsm->id)){
        //                             $newTsm->tsm_id =$tsm->id;
        //                         } 
        //                         $newTsm->save();
        //                      }
                            
        //                  }

        //              }
        //             //  DB::commit();

        //              if ($errorMessage == '') {
        //                  return redirect()->route($this->getRoute())->with('success', 'Imported was success!');
        //              }
        //              return redirect()->route($this->getRoute())->with('warning', 'Imported was success! <br><b>Note: We do not import this data  because</b><br>' . $errorMessage);
        //          }
        //          return redirect()->route($this->getRoute())->with('error', 'Import failed! You are using the wrong CSV format. Please use the CSV template to import your data.');
        //      }
        //      return redirect()->route($this->getRoute())->with('error', 'Please choose file with .CSV extension.');
        //  }

        //  return redirect()->route($this->getRoute())->with('error', 'Please select CSV file.');
        
        // }




    } catch (Exception $e) {
        // Create is failed
        return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
    }


        //old code
        // // If file extension is 'csv'
        // if ($request->hasFile('import')) {
        //     $file = $request->file('import');

        //     // File Details
        //     $extension = $file->getClientOriginalExtension();

        //     // If file extension is 'csv'
        //     if ($extension == 'csv') {
        //         $fp = fopen($file, 'rb');

        //         $header = fgetcsv($fp, 0, ',');
        //         $countheader = count($header);

        //         // Check is csv file is correct format
        //         if ($countheader < 6 && in_array('email', $header, true) && in_array('first_name', $header, true) && in_array('last_name', $header, true) && in_array('role', $header, true) && in_array('password', $header, true)) {
        //             // Loop the row data csv
        //             while (($csvData = fgetcsv($fp)) !== false) {
        //                 $csvData = array_map('utf8_encode', $csvData);

        //                 // Row column length
        //                 $dataLen = count($csvData);

        //                 // Skip row if length != 5
        //                 if (!($dataLen == 5)) {
        //                     continue;
        //                 }

        //                 // Assign value to variables
        //                 $email = trim($csvData[0]);
        //                 $first_name = trim($csvData[1]);
        //                 $last_name = trim($csvData[2]);
        //                 $name = $first_name . ' ' . $last_name;
        //                 $role = trim($csvData[3]);

        //                 // Insert data to users table
        //                 // Check if any duplicate email
        //                 if ($this->checkDuplicate($email, 'email')) {
        //                     $errorArr[] = $email;
        //                     $str = implode(", ", $errorArr);
        //                     $errorMessage = '-Some data email already exists ( ' . $str . ' )';
        //                     continue;
        //                 }

        //                 $password = trim($csvData[4]);
        //                 $hashed = bcrypt($password);

        //                 $data = array(
        //                     'email' => $email,
        //                     'name' => $name,
        //                     'role' => $role,
        //                     'password' => $hashed,
        //                     'image' => 'default-user.png',
        //                 );

        //                 // create the user
        //                 $createNew = User::create($data);

        //                 // Attach role
        //                 $createNew->roles()->attach($role);

        //                 // Save user
        //                 $createNew->save();
        //             }

        //             if ($errorMessage == '') {
        //                 return redirect()->route($this->getRoute())->with('success', 'Imported was success!');
        //             }
        //             return redirect()->route($this->getRoute())->with('warning', 'Imported was success! <br><b>Note: We do not import this data data because</b><br>' . $errorMessage);
        //         }
        //         return redirect()->route($this->getRoute())->with('error', 'Import failed! You are using the wrong CSV format. Please use the CSV template to import your data.');
        //     }
        //     return redirect()->route($this->getRoute())->with('error', 'Please choose file with .CSV extension.');
        // }

        // return redirect()->route($this->getRoute())->with('error', 'Please select CSV file.');
    }

    /**
     * Function check email is exist or not.
     *
     * @param $data
     * @param $typeCheck
     * @return bool
     */
    public function checkDuplicate($data, $typeCheck)
    {
        if ($typeCheck == 'email') {
            $isExists = User::where('email', $data)->first();
        }

        if ($typeCheck == 'name') {
            $isExists = History::where('name', $data)->first();
        }

        if ($isExists) {
            return true;
        }

        return false;
    }


    // public function routeAttendance()
    // {
    //     $data = User::find($id);
    //     $data->form_action = $this->getRoute() . '.create';
       

    //     return view('backend.users.staff_attendance', [
    //         'data' => $data,
    //     ]);
    // }

    public function routeAttendance($id)
    {
        // $userId = Auth::user()->id;
        $data    = User::where('status',0)->find($id);
        $area    = Area::where('id',$data->area_id)->first();
        $holiday = Holiday::get();
        $data->form_action = $this->getRoute() . '.routeAttendanceUpdate';
        $data->button_text = 'Add';

        return view('backend.users.staff_attendance', [
            'data'   => $data,
            'area'   => $area,
            'holiday'=>$holiday
        ]);
    }

    public function routeAttendanceUpdate(Request $request)
    {
        $input = $request->all();
        //check user attendance
        // $checkAttdance = Attendance::where('worker_id',$request->worker_id)->whereIn('status',[1,2,3,4,5,6,7])->first();
        // if(!empty($checkAttdance)){
           
        //     return redirect()->route($this->getRoute())->with('success', Config::get('const.ERROR_ATTENDANCE_MESSAGE'));
        // }

        $user = User::where('id',$request->worker_id)->update(['status'=>1]);
        $location = DB::table('location_coordinates')->where('area_id', $request->area_id)->get(['lat', 'long']);
        $attendance = Attendance::where('worker_id',$request->worker_id)->where('date',date('Y-m-d'))->where('status_updated_by',Auth::user()->id)->first();
        if(!empty($attendance)){
               $attendance->worker_id =$request->worker_id ?? NULL;
               $attendance->status_updated_by =Auth::user()->id ?? NULL;
               $attendance->save();
               return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_ATTENDANCE_MESSAGE'));
        }else{
            foreach($request['date'] as $key=>$value){
                $save = new Attendance();
                $save->worker_id        = $request->worker_id ?? NULL;
                $save->worker_device_id = $request->device_id ?? NULL;
                $save->worker_role_id   = $request->role ?? NULL;
                $save->date             = date('Y-m-d',strtotime($value)) ?? NULL;
                $save->status           = $request['status'][$key] ?? NULL;
                $save->in_location_id   = $request->area_id ?? NULL;
                $save->in_time          = date('H:i:s') ?? NULL;
                $save->late_time        = NULL;
                $save->status_updated_at   = Carbon::now();
                $save->status_updated_by   = Auth::user()->id;
                $save->in_lat_long      = $location[0]->lat.",".$location[0]->long ?? Null;
                $save->save();
                
            }
            
            if($save->save()){
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_ATTENDANCE_MESSAGE'));
            }else{
                return redirect()->back();
            }
        
        }
        
    }

    
}
