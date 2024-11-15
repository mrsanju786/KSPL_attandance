<?php

namespace App\Mail;

use App\Models\Leave;
use App\Models\LeaveLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApproveLeaveMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leaveApprove;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($leaveApprove)
    {
        $this->leaveApprove=$leaveApprove;
      
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = ($this->leaveApprove['leave_status'] == 'Approved') ? '✌️Leave Approved😊' : 'Leave Rejected😌';

        return $this->markdown('emails.approveleavemail')->subject($subject)->with(['data' => $this->leaveApprove]);
      
    }
}
