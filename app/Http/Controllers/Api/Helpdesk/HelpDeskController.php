<?php

namespace App\Http\Controllers\Api\HelpDesk;
use Auth;
use Config;
use Response;
use App\Models\Helpdesk;
use App\Models\TsmEmp;
use App\Models\User;
use App\Mail\HelpDeskMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HelpDeskController extends Controller
{
    public function index (){

        $helpdesk=Helpdesk::all();
        $data = [
            'status' => 'success',
            'message' => 'Helpdesk Data fetched Successfully',
            'helpdesk' => $helpdesk
        ];
        return response()->json($data, 200);
    }



    public function store(Request $request){
        
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
        ]);
        

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message'=>$validator->errors()], 400);
        }

        try {
            $user = Auth::user();
            $helpdesk=new Helpdesk();
            $helpdesk->user_id=$user->id;
            $helpdesk->topic=$request->topic;
            $helpdesk->description=$request->description;

            // ticket number
            $cnt = Helpdesk::whereNotNull('ticket_no')->count();
            $ticket_no = "T".str_pad((int)$cnt+1, 4, 0, STR_PAD_LEFT);
            $helpdesk->ticket_no= $ticket_no;
            // ticket end
             // upload image
            //  $images=[];
            //  $i=0;

             if ($request->hasFile('photos')) {
                $photo = $request->file('photos');
                 
                   
                // foreach($photos as $photo){
                    $file=$photo;
                    $file_name = time().'_'.$helpdesk->user_id . "helpdesk_photos." . $file->getClientOriginalExtension();
                    // save photos to the path
                    $file->move(Config::get('const.UPLOAD_PATH'), $file_name);
                    // $images[]=$file_name;
                //     $i++;
                // }
                // $all_images=implode(',',$images);

                $helpdesk->images=$file_name;
            } else {
                $helpdesk->images=Null;
               
            }

            
            // return $helpdesk->user_id;
            



            $helpdesk->save();

            //email section
            
            // $datahp=Helpdesk::find($helpdesk->id);
            // if($request->file('photo')){
            //     $pathtofile=public_path('uploads/'.$datahp->images);
            // }
            // else{
            //     $pathtofile=Null;
            // }

            // if (env('HELPDESK_MAIL')) {
            //    $to_mail=env('HELPDESK_MAIL');
            // }
            // Mail::to($to_mail)
            // ->send(new HelpDeskMail($helpdesk,$pathtofile));

            $data = [
                'status'=>'success',
                'message' => 'Data saved successfully',
            ];
            return response()->json($data, 200);
        } catch (Exception $e) {
            // Create is failed
            return response()->json(["status"=>"error", "message" => "something went wrong" ], 400);
        }
    }

    public function ticket_list($id=NULL){
        if($id == NULL){
            $user_id = Auth::user()->id;
            $data = Helpdesk::where('user_id', $user_id)->orderBy('id','desc')->get();
            foreach($data as $raw_data){
                if(User::where('id',$raw_data->user_id)->exists()){
                    $u_detail = User::where('id',$raw_data->user_id)->first()->toArray();
                    $raw_data->user_detail = $u_detail;
                }else{
                    $raw_data->user_detail = NULL;
                }
            }
            return response()->json(["status"=>"success", "message"=>"Ticket List fetched Successfully", "data" => $data], 200);
        }else{
            $empIds = TsmEmp::where('tsm_id',$id)->get('emp_id')->toArray();
            $data = Helpdesk::whereIn('user_id',$empIds)->orderBy('id','desc')->get();
            foreach($data as $raw_data){
                if(User::where('id',$raw_data->user_id)->exists()){
                    $u_detail = User::where('id',$raw_data->user_id)->first()->toArray();
                    $raw_data->user_detail = $u_detail;
                }else{
                    $raw_data->user_detail = NULL;
                }
            }
            return response()->json(["status"=>"success", "message"=>"Ticket List fetched Successfully", "data" => $data], 200);
        }
    }

    public function updateTicketStatus(Request $request){
        $helpdesk = Helpdesk::where('id', $request->id)->first();
        if($helpdesk){
            $helpdesk->status = $request->status;
            $helpdesk->updated_at = Carbon::now();
            $helpdesk->updated_by = Auth::user()->id;
            $helpdesk->save();
            return response()->json(["status"=>"success", "message" => "Status Updated successfully" ], 200);
        }else{
            return response()->json(["status"=>"error", "message" => "Helpdesk entry not found" ], 400);
        }
                 
    }

}
