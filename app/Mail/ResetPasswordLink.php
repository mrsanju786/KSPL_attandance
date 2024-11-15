<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordLink extends Mailable
{
    use Queueable, SerializesModels;
    public $user,$url;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user ,$url)
    {
        $this->user=$user;
        $this->url=$url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.auth.reset_email_link')->subject('Reset Password');
    }
}
