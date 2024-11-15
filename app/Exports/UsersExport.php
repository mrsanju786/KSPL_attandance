<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Role;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;
use Session;

class UsersExport implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function view(): View
    {
        $date1 = Session::get('date1');
        $date2 = Session::get('date2');

        $query = DB::table('attendances')
                ->leftjoin('users', 'attendances.worker_id', '=', 'users.id')
                ->leftjoin('areas','users.area_id', '=', 'areas.id')
                ->leftjoin('attendance_statuses', 'attendance_statuses.id','=','attendances.status')
                ->select('attendances.*', 'users.name as user_name', 'users.emp_id as emp_id', 'areas.address','areas.id as area_id','attendance_statuses.name as attnName');
                if($date1 != null){
                    $query = $query->where('date','>=',$date1);
                }
                if($date2 != null){
                    $query = $query->where('date','<=',$date2);
                }
                $query = $query->orderBy('date','ASC')->get();

        return view('backend.exports.attdence', [


            'attdence' => $query

            // 'attdence' => User::
            //     join('attendances', 'attendances.worker_id', '=', 'users.id')
            //     ->select('attendances.*', 'users.name as user_name')
            //     // ->where('')
            //     // ->where('attendances.worker_role_id', [3, 7, 8])
            //     // ->distinct('attendances.worker_id')
            //     // ->groupBy(DB::raw('DATE(attendances.date)'))
            //     // ->unique()
                
            //     ->groupBy('attendances.worker_id')
            //     // ->groupBy('attendances.date')
            //     ->orderBy('attendances.date', 'DESC')


            //     ->get()
                
        ]);
    }
    public function try()
    {
        $cat = Attendance::all();
        // return $cat;



        $abc = array();

        if (count($cat)) {
            foreach ($cat as $cot) {



                $abc[$cot->worker_id] = User::join('roles', 'roles.id', '=', 'users.role')
                    ->join('attendances', 'attendances.worker_id', '=', 'users.id')
                    ->select('attendances.*', 'users.name as user_name', 'roles.display_name as role_name')
                    ->where('users.id', $cot->worker_id)
                    ->whereIn('roles.id', [3, 7, 8])
                    //->groupBy('attendances.worker_id')
                    ->groupBy('attendances.worker_id')
                    ->get();
            }
            // return $abc;
            echo '<pre>';
            print_r($abc);
            die();
            // echo '</pre>';
        }
    }
}
