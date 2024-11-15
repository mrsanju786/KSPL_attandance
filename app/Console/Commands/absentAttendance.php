<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Setting;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class absentAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absent:Attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Absent attendance store';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $getSetting = Setting::find(1);
        $today = Carbon::today()->format('Y-m-d');

        $is_holiday=0;
        $holidays=Holiday::where('status',1)->get();

        foreach($holidays as $key=>$holiday ){
            $holiday_from_date=$holiday->from_date;
            $holiday_to_date=$holiday->to_date;
            $period = CarbonPeriod::create($holiday_from_date,$holiday_to_date);
            $dates=[];
            foreach ($period as $date) {
               array_push($dates,$date->format('Y-m-d'));
            }
            if(in_array($today, $dates)){
                $is_holiday=1;
            }
            else{
                $is_holiday=0;
            }
        }


        $userslist = User::whereIn('role',[3,4,5,6,7,8])->pluck('id')->toArray();
        // Log::debug($userslist);
        $staffAttendance = Attendance::whereIn('worker_id', $userslist)->where('date', $today)->pluck('worker_id')->toArray();
        // Log::debug($staffAttendance);
        $absentStaff = array_diff($userslist, $staffAttendance);
        // Log::debug($absentStaff);
        $absentStaffList=User::whereIn('id',$absentStaff)->get();
        // Log::debug($absentStaffList);
        foreach($absentStaffList as $key=>$user){
            $absentUser=new Attendance();
            $absentUser->worker_id=$user->id;
            $absentUser->worker_role_id=$user->role;
            $absentUser->worker_device_id=$user->device_id;
            $absentUser->date=Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d');

            if(Carbon::now()->dayOfWeek == Carbon::SUNDAY){
                $absentUser->status=6; 
            }
            elseif($is_holiday==1){
                $absentUser->status=4;
            }
            else{
                $absentUser->status=2;
            }
            $absentUser->save();
        }
    }
}
