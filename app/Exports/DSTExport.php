<?php

namespace App\Exports;
use App\Models\Area;
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
use Carbon\Carbon;


class DSTExport implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function view(): View
    {
        $date1 = Session::get('date1');
        $date2 = Session::get('date2');
        $absentUser =[];
        // $all_users = DB::table('users')
        // ->whereIn('role', [8])->get('id');
        // foreach($all_users as $users){
        //     $chk_users = DB::table('attendances')->whereBetween('date', [$date1, $date2])->where('worker_id', $users->id)->first();
        //     if(!isset($chk_users)){
        //         $absentUser[] = $users->id;
        //     }
        // }

        $all_users = User::where('role', 8)
                            ->pluck('id')
                            ->toArray();

        $attendance =Attendance::whereBetween('date', [$date1, $date2])
                                ->whereIn('worker_id', $all_users)
                                ->pluck('worker_id')
                                ->toArray(); 

        $allUsers =[];
        $allUsers= array_merge($attendance,$all_users);

        $activeUsers = User::whereIn('id',$allUsers)
                            ->where('status',1)
                            ->pluck('id')
                            ->toArray();
        
        $deactiveusers = User::whereIn('id',$allUsers)
                                ->where('status',0)
                                ->whereBetween('deactivated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                                ->pluck('id')
                                ->toArray();

        $totalUser  =  array_merge($activeUsers,$deactiveusers); 

        $absentUser      =   User::whereIn('id',$totalUser)->get();   
        // $absentUser = DB::table('users')
        // ->whereIn('id', $absentUser)->get();
        foreach($absentUser as $usersDetails){
            $addr = Area::where('id', $usersDetails->area_id)->first('address');
            $usersDetails->worker_role_id = $usersDetails->role;
            $usersDetails->address = $addr->address ?? "-";
            $usersDetails->worker_device_id = null;
            $usersDetails->in_time = null;
            $usersDetails->date = null;
            $usersDetails->user_name = $usersDetails->name;
            $usersDetails->user_status = $usersDetails->status;
            $usersDetails->worker_id = $usersDetails->id;
            $usersDetails->attnId = 2;
        }

        $query = DB::table('attendances')
                ->leftjoin('users', 'attendances.worker_id', '=', 'users.id')
                ->leftjoin('areas','users.area_id', '=', 'areas.id')
                ->leftjoin('attendance_statuses', 'attendance_statuses.id','=','attendances.status')
                ->select('attendances.*', 'users.name as user_name','users.status as user_status', 'users.emp_id as emp_id', 'areas.address','areas.name as sole_id','attendance_statuses.name as attnName','attendance_statuses.id as attnId','users.area_id as areaId','users.id as user_id')
                ->whereIn('attendances.worker_role_id', ['8']);
                if($date1 != null){
                    $query = $query->where('date','>=',$date1);
                }
                if($date2 != null){
                    $query = $query->where('date','<=',$date2);
                }
                $query = $query->where('users.status',1)
                                //->whereBetween('users.deactivated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                                ->orderBy('date','ASC')
                                ->get();
                $dates = getBetweenDates($date1, $date2);

        return view('backend.exports.attdence', [

            'attdence' => $query, 'absent_users'=>$absentUser, 'dates'=>$dates

            // 'attdence' => User::join('roles', 'roles.id', '=', 'users.role')
            //     ->join('attendances', 'attendances.worker_id', '=', 'users.id')
            //     ->select('attendances.*', 'users.name as user_name', 'roles.display_name as role_name')
            //     // ->where('')
            //     ->whereIn('attendances.worker_role_id', [8])
            //     // ->distinct('attendances.worker_id')
            //     ->orderBy('attendances.date', 'DESC')
            //     // ->groupBy(DB::raw('DATE(attendances.date)'))
            //     // ->unique()

            //     ->groupBy(DB::raw('(attendances.worker_id)'))
            //     // ->groupBy('attendances.date')


            //     ->get()
        ]);
    }
}
function getBetweenDates($startDate, $endDate) {
    $rangArray = [];
 
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);
 
    for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
        $date = date('Y-m-d', $currentDate);
        $rangArray[] = $date;
    }
 
    return $rangArray;
} 
