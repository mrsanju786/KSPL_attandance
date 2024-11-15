<?php

namespace App\Console\Commands;

use Excel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Models\LeaveLog;
use App\Models\Area;
use SerializesModels;
use App\Models\Attendance;
use App\Mail\DailyAttendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\RsmTsm;
use App\Models\TsmEmp;
use Illuminate\Support\Facades\Mail;
use App\Models\AttendanceDailyExport;

class CmtDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:daily_attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Staff Daily attendence';

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
        $currentDate =Carbon::now();
        // $start_month =Carbon::parse($currentDate)->toDateString();
        // $end_month   =Carbon::parse($currentDate)->toDateString();
        $start_month =$currentDate->startOfMonth()->toDateString();
        $end_month   =$currentDate->endOfMonth()->toDateString();
        Log::debug($start_month);
        Log::debug($end_month);
        $allUser =[];
      
        $user       = User::whereIn('role',[3,5,6])->pluck('id')->toArray();
        $attendance = Attendance::whereBetween('date', [$start_month, $end_month])->whereIn('worker_id', $user)->whereIn('worker_role_id',[3,5,6])->pluck('worker_id')->toArray(); 

        $allUser    = array_merge($attendance,$user);
       
        //get all users
        $activeUsers = User::whereIn('id',$allUser)->whereIn('role',[3,5,6])
                            ->where('status',1)
                            ->pluck('id')
                            ->toArray();
        
        $deactiveusers = User::whereIn('id',$allUser)->whereIn('role',[3,5,6])
                            ->where('status',0)
                            ->whereBetween('deactivated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                            ->pluck('id')
                            ->toArray();

        $totalUser  =  array_merge($activeUsers,$deactiveusers); 

        $users      =   User::whereIn('id',$totalUser)->get();   
      
        $period = CarbonPeriod::create($start_month, $end_month);

        $dates=[];
 
        foreach ($period as $date) {
            array_push($dates,$date->format('Y-m-d'));
        }

        $data=[];
        $today = date('Y-m-d');
        foreach ($users as $key => $user) {
         
            $areas = Area::where('id',$user->area_id)->first();
            $areas_address = isset($areas->address)?$areas->address:'';
            $areas_name = isset($areas->name)?$areas->name:'';
            $payable_days="0";
            $rsm_name =Null;
            $rsm_name1 =Null;
            //tsm rsm name
            $emp  = DB::table('rsm_tsms')->where('tsm_id',$user->id)->pluck('rsm_id')->toArray();
            $tsm  = DB::table('tsm_emps')->where('emp_id',$user->id)->pluck('tsm_id')->toArray();
            if(!empty($emp)){
                $rsm_name = DB::table('users')->whereIn('id',$emp)->where('role',6)->first();
            
            }
            if(!empty($tsm)){
                $rsm_name1 = DB::table('users')->whereIn('id',$tsm)->where('role',6)->first();

                $tsmEmp = RsmTsm::where('tsm_id',$tsm)->pluck('rsm_id')->toArray();
                if(!empty($tsmEmp)){
                    $rsm_name1 = DB::table('users')->whereIn('id',$tsmEmp)->where('role',6)->first();
                }
            }

            $i="0";
            $pa="0";
            $od="0";
            $l="0";
            $a="0";
            $wo="0";
            $pl="0";
            $h="0";
            $payable_days1 ="0";
            $payable_days2 ="0";
            $data[$key][]=$user->emp_id;
            $data[$key][]=$user->name;
            if($user->role ==3){
                $data[$key][]=$rsm_name1->name ?? "-";
            }elseif($user->role ==5){
                $data[$key][]=$rsm_name->name ?? "-";
            }else{
                $data[$key][]="-";
            } 
            $data[$key][]=$user->status == 0 ? "Deactive" : "Active";
            $data[$key][]=$areas_name ?? '-';
            $data[$key][]=$user->device_id == null ? '' : $user->device_id;
            $data[$key][]=$areas_address ?? '-';
            foreach ($dates as  $date_key=>$date_value) {
               //leave attendance add PL in reports
                $leave = LeaveLog::where('user_id',$user->id)->where('status',1)->where('from_date', $date_value)->first();
                //holidat attendance
                $userarea  = DB::table('users')->where('id',$user->id)->where('status',1)->first();
                if(!empty($userarea)){
                    $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
                }
                
                $holiday =Null;
                if(!empty($areastate)){
                    $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
                    ->where('date',$date_value)     
                    ->first();
                }    
                $attendance = Attendance::where('date', $date_value)->where('worker_id',$user->id)->first();
                
                if($attendance==null){
                    if(Carbon::parse($date_value)->dayOfWeek == Carbon::SUNDAY){
                        $wo = $wo+1;
                        $data[$key][]='WO';

                    }elseif($leave != null){
                        $pl = $pl+1;
                        $data[$key][]='PL';
                    }
                    elseif($holiday != null){
                        $h = $h+1;
                        $data[$key][]='H';
                    }
                    else{
                        $a = $a+1;
                        $data[$key][]='A';
                    }

                    // $j++;
                }
                else{
                    
                    // $data[$key][]=$attendance->attendanceStatus->name;
                    if($attendance->attendanceStatus->id=='1'){
                        $pa = $pa+1;
                        // $payable_days=$i+1 ;
                        $data[$key][]='P';
                    }
                    elseif($attendance->attendanceStatus->id=='2'){
                        $a = $a+1;
                        $data[$key][]='A';
                    }  
                    elseif($attendance->attendanceStatus->id=='3'){
                        $od = $od+1;
                        // $payable_days1=$payable_days+1 ;
                        $data[$key][]='OD';
                    } 
                    elseif($attendance->attendanceStatus->id=='4'){
                        $h = $h+1;
                        $data[$key][]='H';
                    } 
                    elseif($attendance->attendanceStatus->id=='5'){
                        $a = $a+1;
                        $data[$key][]='A';
                    }
                    elseif($attendance->attendanceStatus->id=='6'){
                        $wo = $wo+1;
                        $data[$key][]='WO';
                    }  
                    elseif($attendance->attendanceStatus->id=='7'){
                        $l = $l+1;
                        // $payable_days2=$payable_days1+1 ;
                       
                        $data[$key][]='LT';
                    }
                    elseif($attendance->attendanceStatus->id=='8'){
                        $pl = $pl+1;
                       
                        $data[$key][]='PL';
                    }
                    $i++;
                   
                }
            }
            
            //count pa, lt, and od payable days
            $payable_days += ($pa + $l + $od + $wo + $h);
            $data[$key][]=!empty($payable_days) ? $payable_days:"0";
            $data[$key][]=!empty($pa)      ? $pa :"0";
            $data[$key][]=!empty($l)       ? $l  :"0";
            $data[$key][]=!empty($od)      ? $od :"0";
            $data[$key][]=!empty($wo)      ? $wo :"0";
            $data[$key][]=!empty($pl)      ? $pl :"0";
            $data[$key][]=!empty($h)       ? $h  :"0";        
            $data[$key][]=!empty($a)       ? $a  :"0";
              
           
        }       
        $cc = [];
        if (env('TNM_EMAIL_CC')) {
            $cc = array_merge($cc, explode(',', env('TNM_EMAIL_CC')));
        }
        $user=User::where('role',9)->first();
        Mail::to($cc)->send(new DailyAttendance($user, $start_month,$end_month,$data));
        Log::debug('Mail sent successfully!');

    }

}
