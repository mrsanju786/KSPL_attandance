<?php

namespace App\Mail;

use App\Models\Helpdesk;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class HelpDeskMail extends Mailable
{
    use Queueable, SerializesModels;

    public $helpdesk ,$pathtofile;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Helpdesk $helpdesk ,$pathtofile)
    {
        $this->helpdesk=$helpdesk;
        $this->pathtofile=$pathtofile;
    
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        if($this->helpdesk->images!=NULL){
           
            return $this->markdown('emails.helpdeskmail')->subject('New Ticket has been raised')->attach($this->pathtofile);
        }
        else{
            Log::debug('sddbcvvbbbb');
            return $this->markdown('emails.helpdeskmail')->subject('New Ticket has been raised'); 
        }
       
    }
}
