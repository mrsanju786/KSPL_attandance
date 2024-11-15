<?php

namespace App\Http\Controllers\Auth;

use DB;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Mail\ResetPasswordLink;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;


class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
            $user = User::where('email', $request->email)->first();

            if($user){
                 //Generate a random string.
                $token = openssl_random_pseudo_bytes(70);

                //Convert the binary data into hexadecimal representation.
                $token = bin2hex($token);

                $user->reset_token = $token;
                $user->reset_token_expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                $user->save();

                $url=url('reset_password/'.$user->reset_token.'/'. $user->email);
                Mail::to($user->email)
                ->queue(new ResetPasswordLink($user ,$url));
                return redirect('password/reset')->with('success','Email Sent Successsfully');
            }
            else{
                return redirect('password/reset')->with('error','No account exists with this email');
            }

    }
}
