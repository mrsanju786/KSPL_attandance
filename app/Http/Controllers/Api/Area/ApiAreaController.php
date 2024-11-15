<?php

namespace App\Http\Controllers\Api\Area;

use Response;
use App\Models\Area;
use App\Models\RsmTsm;
use App\Models\TsmEmp;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class ApiAreaController extends Controller
{
    /**
     * API data Area
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataArea = Area::all('id', 'name');
        // Get only assigned areas to tsm or rsm
        if (Auth::user()->role != 5 || Auth::user()->role != 6) {
            $dataArea = Auth::user()->areas;
        }
        if ($dataArea->count() > 0) {
            $data = [
                'status' => 'success',
                'message' => 'Successfull!',
                'area' => $dataArea
            ];
            return response()->json($data, 200);
        }

        $data = [
            'status' => 'error',
            'message' => 'No Area assigned'
        ];
        return response()->json($data, 400);
    }

    public function tsms_list($id){
        $tsm_ids = RsmTsm::where('rsm_id', $id)->get('tsm_id')->toArray();
        $data = User::whereIn('id', $tsm_ids)->where('status',1)->with('area')->select('id','name','email','emp_id','area_id')->get();
        return response()->json(['status' => 'success', 'message' => 'Successfull!', 'data' => $data], 200);
    }

    public function staff_list($id){
        $tDate = Carbon::today()->format('Y-m-d');
        $emp_ids = TsmEmp::where('tsm_id', $id)->get('emp_id')->toArray();
        
        $data = User::whereIn('id', $emp_ids)->where('status',1)->with('area')->select('id','name','email','emp_id','area_id')->get();
        foreach($data as $datas){
            $attendance = Attendance::where('worker_id', $datas->id)->where('date', $tDate)->select('id','date','in_time','status','reason')->first();
            if($attendance){
                $datas->attendance = $attendance;
            }else{
                $datas->attendance = NULL;
            }
            
        }
        
        return response()->json(['status' => 'success', 'message' => 'Successfull!', 'data' => $data], 200);
    }

    public function self_list($id){
        $tDate = Carbon::now()->subDays(20);
        $data = User::where('id',$id)->select('id','name','email','emp_id','area_id')->whereNotIn('role',[1,2,4,9])->where('status',1)->first();
        $data->attendance = Attendance::where('worker_id', $id)->where('date', '>=' , $tDate)->select('date','in_time','out_time','status','image','in_lat_long','is_odd')->whereNotIn('worker_role_id',[1,2,4,9])->orderBy('date', 'desc')->get();
        $data->latlong =DB::table('location_coordinates')->where('area_id',$data->area_id)->select('lat','long')->first();
        return response()->json(['status' => 'success', 'message' => 'Successfull!','image_url'=>env('IMAGE_URL')."public/uploads/",'data' => $data], 200);
    }
    
    public function date_attendance(Request $request){
        $id = Auth::user()->id;
        if(isset($request->userID)){
            $id = $request->userID;
        }
        $Date = $request->date;
        $data = User::where('id',$id)->select('id','name','email','emp_id','area_id')->whereNotIn('role',[1,2,4,9])->where('status',1)->first();

        $attendance = Attendance::where('worker_id', $id)->where('date', '=' , $Date)->select('date','in_time','out_time','status','image')->whereNotIn('worker_role_id',[1,2,4,9])->orderBy('date', 'desc')->get();

        return response()->json(['status' => 'success', 'message' => 'Successfull!','image_url'=>env('IMAGE_URL')."public/uploads/",'attendance' => $attendance], 200);
    }
}
