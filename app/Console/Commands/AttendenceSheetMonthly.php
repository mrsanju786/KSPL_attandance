<?php

namespace App\Console\Commands;

use Excel;
use Carbon\Carbon;
use App\Models\User;
use SerializesModels;
use App\Models\Attendance;
use App\Mail\MonthlyAttendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


use Illuminate\Support\Facades\Mail;
use App\Models\AttendanceMonthlyExport;

class AttendenceSheetMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monthly:attendence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monthly attendence list';

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
        $currentDate=Carbon::now();
        $start_month=Carbon::parse($currentDate)->firstOfMonth()->toDateString();
        $end_month=Carbon::parse($currentDate)->endOfMonth()->toDateString();
        $attendances=Attendance::whereBetween('date', [$start_month, $end_month])->get();
        // Log::Debug($attendances);
         
        // $attachment=(new AttendanceMonthlyExport)->download('sheet.csv', \Maatwebsite\Excel\Excel::CSV);
        // hr role
        $user=User::where('role',2)->first();
        Mail::to($user->email)
        ->send(new MonthlyAttendance($user, $attendances));
        //  return Command::SUCCESS;

    }

}
