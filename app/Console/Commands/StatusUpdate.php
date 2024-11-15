<?php

namespace App\Console\Commands;

use App\Models\AttendanceRegularization;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StatusUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:update_status_for_leave_and_regularization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Leave and Regularization status change for system approved or syatem reject';

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
        $today = Carbon::now();
        $lastSevenDays = $today->subDays(7);

        //Regularization 
        // $regularizations = AttendanceRegularization::where('status',0)->whereBetween('created_at',[$lastSevenDays,$today])->get();
        $regularizations = AttendanceRegularization::where('status',0)->where('created_at','<',$lastSevenDays)->get();
        foreach($regularizations as $data)
        {
            AttendanceRegularization::where('id',$data->id)->update(['status' => 1,'system_status'=>1]);
        }
        //Leave
        $leaves = Leave::where('status',0)->where('created_at','<',$lastSevenDays)->get();
        foreach($leaves as $leave)
        {
             Leave::where('id',$leave->id)->update(['status' => 1,'system_status'=>1]);
        }
        
        Log::debug('regularization and leave status update successfully!');
    }
}
