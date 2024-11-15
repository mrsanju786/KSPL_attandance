<?php

namespace App\Http\Controllers\Api\Setting;
use Auth;
use DateTime;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;


class SettingsController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function selfieStatus(Request $request){
        $setting = Setting::select('selfie_allowed')->find(1);
        $selfieAllowed = (bool) $setting->selfie_allowed;
        return response()->json(['status' => 'success', 'message' => 'Selfie Status', 'data' => ['selfie_allowed' => $selfieAllowed]], 200);
    }

}    