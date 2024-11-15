<?php

namespace App\Http\Controllers\Backend\Roles;

use App\Models\AssignRole;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Auth;
use Config;
use DB;
use Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'Role ID', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            
            'display_name' =>['title'=>'Role Name'],
            'created_at',
            'updated_at',
            'last_updated_by',
            'action' => ['orderable' => false, 'searchable' => false],
        ];

       
       
        if ($datatables->getRequest()->ajax()) {

            $query = Role::query();
        
            if (Auth::user()->hasRole('administrator')) {

                $assignRole = AssignRole::where(['company_id' => Auth::user()->company_id])->first();
        
                if ($assignRole) {
                    // If a record exists, get roles from AssignRole table
                    $assignRoles = AssignRole::select(['role_id as id', 'display_name','last_updated_by', 'created_at', 'updated_at'])
                        ->where(['company_id' => Auth::user()->company_id])
                        ->get()
                        ->toArray();
                  
                    $roles = Role::whereNotIn('id', [10])
                        ->whereNotIn('id', array_column($assignRoles, 'id'))
                        ->get()
                        ->toArray();

                    $query = array_merge($assignRoles, $roles);
                
                    usort($query, function ($a, $b) {
                        return $a['id'] - $b['id'];
                    });
                   
                } else {
                    // If no record exists, get roles from Role table excluding id 10
                    $query = Role::whereNotIn('id', [10])
                        ->get()
                        ->toArray();
                }
            }
        
            return $datatables->of($query)
            ->addColumn('last_updated_by', function ($data) {

                // Check if $data is an instance of Role
                    if (is_array($data) && array_key_exists('last_updated_by', $data)) {
                        if ($data['last_updated_by']) {
                            $userInfo = User::where('id', $data['last_updated_by'])->orderBy('id','ASC')->first();
                            return $userInfo ? $userInfo->name : "N/A";
                        } else {
                            return "N/A";
                        }
                    } else {
                        // Handle the case where $data is not an instance of Role
                        return "N/A";
                    }
               })
                ->addColumn('created_at', function ($data) {
                    return date('d-m-Y', strtotime($data['created_at'])) ?? "-";
                })
                ->addColumn('updated_at', function ($data) {
                    return date('d-m-Y', strtotime($data['updated_at'])) ?? "-";
                })
                ->addColumn('action', function ($data) {
                    $button = '<div class="col-sm-12"><div class="row">';
                    if (Auth::user()->hasRole('administrator')) {
                        $button .= '<div class="col-sm-6"><button class="btn btn-primary" data-toggle="modal" data-target="#editModal" data-id="' . $data['id'] . '"
                        data-display-name="' . $data['display_name'] . '"><i class="fa fa-edit"></i></button></div>';
                    } else {
                        $button .= '<div class="col-sm-6"><button class="btn btn-primary disabled"><i class="fa fa-edit"></i></button></div>';
                    }
                    $button .= '</div></div>';
        
                    return $button;
                })
                ->rawColumns(['action'])
                ->toJson();
        }
        
        

        $columnsArrExPr = [0,1,2,3,4,5];
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

        return view('backend.roles.index', compact('html'));
    }

    public function buttonDatatables($columnsArrExPr)
    {
        $fileName = "roles";
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
     * @param  \App\AssignRole  $assignRole
     * @return \Illuminate\Http\Response
     */
    public function show(AssignRole $assignRole)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AssignRole  $assignRole
     * @return \Illuminate\Http\Response
     */
    public function edit(AssignRole $assignRole)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AssignRole  $assignRole
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $roleId)
    {   

        $assignRoles = AssignRole::where(['role_id'=>$roleId,'company_id'=>Auth::user()->company_id])->first();
        if (AssignRole::where('display_name', 'like', '%' . $request->role_name)->exists()) {
            return response()->json(['success' => false]);
        }        

        if ($assignRoles) {
            $assignRoles->role_id = $roleId;
            $assignRoles->display_name = $request->role_name;
            $assignRoles->company_id = Auth::user()->company_id;
            $assignRoles->last_updated_by = Auth::user()->id;
            $assignRoles->save();
        }else{
            $updateRoles = new AssignRole;
            $updateRoles->role_id = $roleId;
            $updateRoles->display_name = $request->role_name;
            $updateRoles->company_id = Auth::user()->company_id;
            $updateRoles->last_updated_by = Auth::user()->id;
            $updateRoles->save();
        }
        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AssignRole  $assignRole
     * @return \Illuminate\Http\Response
     */
    public function destroy(AssignRole $assignRole)
    {
        //
    }
}
