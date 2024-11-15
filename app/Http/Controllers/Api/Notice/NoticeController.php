<?php

namespace App\Http\Controllers\Api\Notice;
use Auth;
use DateTime;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Notice;


class NoticeController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function noticeList(Request $request){
        $tDate  = Carbon::now()->subDays(10);
        $notice = Notice::where('status',1)->orderBy('id','desc')->where('created_at','>=',$tDate)->get();
        $notice_count = 0;
        $notice_count = Notice::where('status',1)->where('created_at','>=',$tDate)->where('is_read',0)->count();
        return response()->json(['status' => 'success', 'message' => 'Notice List', 'data' => ['notice_list' => $notice,'notice_count'=>$notice_count]], 200);
    }

    public function noticeRead(Request $request){
        
        $noticeRead = Notice::where('status',1)->where('is_read',0)->update(['is_read'=>1]);
        return response()->json(['status' => 'success', 'message' => 'Notice Read successfully!', 'data' => []], 200);
    }

}    