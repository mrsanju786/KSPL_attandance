<?php

namespace App\Mail;

use App\Models\Leave;
use App\Models\LeaveLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApplyLeaveMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leaveData;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($leaveData)
    {
       
        $this->leaveData=$leaveData;
      
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

       
        return $this->markdown('emails.leaveapplymail')->subject('Leaves applied by'.' '.$this->leaveData['username'].' ')->with(['data' => $this->leaveData]);
      
       
    }
}
