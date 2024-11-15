<?php

namespace App\Http\Controllers\Backend\Message;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Config;
use DB;

class MessageController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;
        // $data = User::find($userId);
        $data = User::get('name','mobile_number');

        return view('backend.message.form', [
            'data' => $data
        ]);
    }
}
