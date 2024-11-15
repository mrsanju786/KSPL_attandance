<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use App\Models\AttendanceMonthlyExport;
use Illuminate\Contracts\Queue\ShouldQueue;

class MonthlyAttendance extends Mailable
{
    use Queueable, SerializesModels;

    public $user ,$attendances;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user,  $attendances)
    {
        $this->user=$user;
        $this->attendances=$attendances;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.auth.monthly_attendance')                
                    ->attach(
                        Excel::download(
                            new AttendanceMonthlyExport($this->attendances), 
                            'report.xlsx'
                        )->getFile(), ['as' => 'report.xlsx']
                    );

    }
}
