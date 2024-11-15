<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Attendance;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AttendanceDailyExport;

class DailyAttendance extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user ,$data,$start_month,$end_month;

    public function __construct(User $user, $data,$start_month,$end_month)
    {
        $this->user=$user;
        $this->data=$data;
        $this->start_month=$start_month;
        $this->end_month  =$end_month;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.auth.daily_attendance')                
                    ->attach(
                        Excel::download(
                            new AttendanceDailyExport($this->data,$this->start_month,$this->end_month ), 
                            'DailyAttendanceReport.xlsx'
                        )->getFile(), ['as' => 'DailyAttendanceReport.xlsx']
                    );
    }
}
