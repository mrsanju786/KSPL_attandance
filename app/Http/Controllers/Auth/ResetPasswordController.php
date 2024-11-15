<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;


    public function create(Request $request){
       
        return view('vendor.adminlte.passwords.reset')->with(['token'=>$request->token ,'email'=>$request->email]);
    }


    public function setPassword(Request $request){

        $validator = Validator::make($request->all(), [
            // 'email' => 'required|email',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with(['error'=>$validator->errors()],400);
        }
        
        $user = User::where('email',$request->email)->where('reset_token',$request->token)->first();

        if(!$user){
            return redirect()->back()->with( ['token'=>$request->token ,'error' =>'Email not found/Invalid token.']);
        }

        if(time() > strtotime($user->reset_token_expiry)){
            return redirect()->back()->with( ['token'=>$request->token,'error' =>'Link expired! ']);
        }

        //token verified. Reset to null
        $user->reset_token = NULL;
        $user->reset_token_expiry = NULL;

        //New password set
        $user->password = bcrypt($request->password);
        $user->save();

        return redirect('login')->with('success','Password reset successfully');

    }
}
