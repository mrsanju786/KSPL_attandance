<?php

namespace App\Exports;

use App\Models\User;
use App\Models\UserLog;
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

class EmployeeNotLoggedOneWeek implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function view(): View
    {
        $loggedUser  = UserLog::whereBetween('login_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->pluck('user_id');
        $oneWeekEmployee = User::with('area')->whereNotIn('id',$loggedUser)->whereNotIn('role',[1,2])->where('status',1)->orderBy('id','desc')->get();

        return view('backend.exports.employee_not_logged_one_week', [

            'attdence' => $oneWeekEmployee

        ]);
    }
}
