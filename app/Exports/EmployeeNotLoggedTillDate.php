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

class EmployeeNotLoggedTillDate implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function view(): View
    {
        $notLoggedInEmployee  = User::with('area')->whereNull('device_id')
                                     ->whereNotIn('role',[1,2])
                                     ->where('status',1)
                                     ->orderBy('id','desc')
                                     ->get();

        return view('backend.exports.employee_not_logged_till_date', [

            'attdence' => $notLoggedInEmployee

        ]);
    }
}
