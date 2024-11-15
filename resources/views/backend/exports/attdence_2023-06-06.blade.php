<table>
    <thead>
        <tr>



            <th>Emp Code</th>
            <th>Emp Name</th>
            <th>Rsm Name</th>
            <th>User Status</th>
            <th>Sole Id</th>
            <th>Location</th>
            <th>Work Device Id</th>
            <th>In Time</th>
            
            {{--<th>Role Name</th>--}}

            {{--@foreach($attdence->unique('date') as $value)
            <th>{{$value->date}}</th>
            @endforeach--}}
            
            @foreach($dates as $value)
            <th>{{$value}}</th>
            @endforeach
            <th>Payable Days (P+LT+OD+H+WO)</th>
            <th>Present</th>
            <th>Late</th>
            <th>Out Door</th>
            <th>Week Off</th>
            <th>Leave</th>
            <th>Holiday</th>
            <th>Absent</th>

        </tr>
    </thead>
    <tbody>
        <?php

        // $area_id = []; 
        // $dateArr = array_values($attdence->unique('date')->toArray());

        $dateArr = $dates;
        $cnt = count($dates); ?>
        <!-- //obst  -->
        @foreach($attdence->unique('worker_id') as $key=>$values)
        <?php
            $rsm_name = Null;
            //obst rsm name
            // $emp  = DB::table('rsm_tsms')->where('tsm_id',$values->user_id)->pluck('rsm_id')->toArray();
            $tsm  = DB::table('tsm_emps')->where('emp_id',$values->user_id)->pluck('tsm_id')->toArray();
           
            if(!empty($tsm)){
                $rsm_name = DB::table('users')->whereIn('id',$tsm)->where('role',6)->first();

                $tsmEmp = App\Models\RsmTsm::whereIn('tsm_id',$tsm)->pluck('rsm_id')->toArray();
                if(!empty($tsmEmp)){
                    $rsm_name = DB::table('users')->whereIn('id',$tsmEmp)->where('role',6)->first();
                
                }
            
            }
           
           
        ?>
        @if($values->worker_role_id=='3')
        <tr>
            <td>{{ $values->emp_id }}</td>            
            <td>{{ $values->user_name }}</td>
            <td>{{ !empty($rsm_name->name) ? $rsm_name->name: "-" }}</td>
            <td>{{ $values->user_status == 0 ? "Deactive" : "Active" }}</td>
            <td>{{ !empty($values->sole_id) ? $values->sole_id  : "-" }}</td>
            <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
            <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
            <td> {{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
            <?php
            // $area_id[]=$values->area_id;
            $p_count = 0;
            $A_count = 0;
            $pa=0;
            $od=0;
            $l=0;
            $a=0;
            $wo=0;
            $pl=0;
            $h=0;
            // DB::connection()->enableQueryLog();
           
            // print_r(DB::getQueryLog());
            
            
            ?>
        
            @for($s=0; $s<$cnt; $s++)
            <?php 
             $data = DB::table('attendances')
             ->where('worker_id',$values->worker_id)
             ->where('attendances.worker_role_id', $values->worker_role_id)
             ->where('date', $dateArr[$s])
             ->where('in_time','!=',NULL)
             ->first();
            ?>
            <td>
                <?php
                //leave attendance
                $leave   =  DB::table('leave_logs')->where('user_id',$values->worker_id)
                                    ->where('status',1)
                                    ->where('from_date', $dateArr[$s])
                                    ->first();

                // //holiday attendance
                $userarea  = DB::table('users')->where('id',$values->worker_id)->where('status',1)->first();
                if(!empty($userarea)){
                    $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
                }
                $holiday =Null;
                if(!empty($areastate)){
                    $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
                    ->where('date',$dateArr[$s])     
                    ->first();
                
                }
                // elseif($holiday != null){
                //     $h = $h+1;
                //     echo "H";
                // }
                
                $weekDay = date('w', strtotime($dateArr[$s]));
                if($data ==Null){
                    if($weekDay == 0 || $weekDay == 6){
                        $wo = $wo+1;
                        echo "WO";
                    }elseif($leave != null){
                        $pl = $pl+1;
                        echo 'PL';
                    }
                     
                    elseif($holiday != null){
                        $h = $h+1;
                        echo "H";
                    }

                    else{
                        $a = $a+1;
                        echo "A";
                        
                    }
                }
                else{
                    if(!empty($data->status)){
                        if($data->status=='1'){
                            $pa = $pa+1;
                            echo "P";
                        }

                        elseif($data->status=='2'){
                            $a = $a+1;
                            echo "A";
                        } 
                        
                        elseif($data->status=='3'){
                            $od = $od+1;
                            echo "OD";
                        } 

                        elseif($data->status=='4'){
                            $h = $h+1;
                            echo "H";
                        } 
                       
                        elseif($data->status=='5'){
                            $a = $a+1;
                            echo "A";
                        }
                        elseif($data->status=='6'){
                            $wo = $wo+1;
                            echo "WO";
                        }
                        elseif($data->status=='7'){
                            $l = $l+1;
                            echo "LT";
                        }

                        elseif($data->status=='8'){
                            $pl = $pl+1;
                            echo "PL";
                        }

                        
                        if($data->status=='1' || $data->status=='7' || $data->status=='3'){
                            $p_count++;
                        }
                    }
                }
                ?>
            </td>
            @endfor

            
            <th>
                {{$p_count + $wo + $h}}
            </th>
            <th>
                {{$pa ?? 0}}
            </th>
            <th>
                {{$l ?? 0}}
            </th>
            <th>
                {{$od ?? 0}}
            </th>
            <th>
                {{$wo ?? 0}}
            </th>
            <th>
                {{$pl ?? 0}}
            </th>

             <th>
                {{$h ?? 0}}
            </th>
            <th>
                {{$a ?? 0}}
            </th>

           
        </tr>
        @endif
        @endforeach

        <!-- absent obst start -->
        @foreach($absent_users as $key=>$values)
        <?php  $soleId = DB::table('areas')->where('id',$values->area_id)->first();?>
        <?php 
            $rsm_name = Null;
            //obst rsm name
            // $emp  = DB::table('rsm_tsms')->where('tsm_id',$values->id)->pluck('rsm_id')->toArray();
            $tsm  = DB::table('tsm_emps')->where('emp_id',$values->id)->pluck('tsm_id')->toArray();
            
            if(!empty($tsm)){
                $rsm_name = DB::table('users')->whereIn('id',$tsm)->where('role',6)->first();

                $tsmEmp = App\Models\RsmTsm::whereIn('tsm_id',$tsm)->pluck('rsm_id')->toArray();
                if(!empty($tsmEmp)){
                    $rsm_name = DB::table('users')->whereIn('id',$tsmEmp)->where('role',6)->first();
                
                }
            
            }
            
           
        ?>
        @if($values->worker_role_id=='3')
        <tr>
            <td>{{ $values->emp_id }}</td>            
            <td>{{ $values->user_name }}</td>
            <td>{{ !empty($rsm_name->name) ? $rsm_name->name: "-" }}</td>
            <td>{{ $values->user_status == 0 ? "Deactive" : "Active" }}</td>
            <td>{{ !empty($soleId->name) ? $soleId->name  : "-" }}</td>
            <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
            <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
            <td>{{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
            
            <?php
            // $area_id[]=$values->area_id;
            $p_count = 0;
            $A_count = 0;
            $pa=0;
            $od=0;
            $l=0;
            $a=0;
            $wo=0;
            $pl=0;
            $h=0;
            // DB::connection()->enableQueryLog();
            
            ?>
        
        @for($s=0; $s<$cnt; $s++)
            <?php 
             $data = DB::table('attendances')
             ->where('worker_id',$values->worker_id)
             ->where('attendances.worker_role_id', $values->worker_role_id)
             ->where('date', $dateArr[$s])
             ->where('in_time','!=',NULL)
             ->first();
            ?>
            <td>
                <?php

                //leave attendance
                $leave   =  DB::table('leave_logs')->where('user_id',$values->worker_id)
                                    ->where('status',1)
                                    ->where('from_date', $dateArr[$s])
                                    ->first();
                
                // //holiday attendance
                $userarea  = DB::table('users')->where('id',$values->worker_id)->where('status',1)->first();
                if(!empty($userarea)){
                    $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
                }
                $holiday =Null;
                if(!empty($areastate)){
                    $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
                    ->where('date',$dateArr[$s])     
                    ->first();
                
                }
                
                $weekDay = date('w', strtotime($dateArr[$s]));
                if($data ==Null){
                    if($weekDay == 0 || $weekDay == 6){
                        $wo = $wo+1;
                        echo "WO";
                    }elseif($leave != null){
                        $pl = $pl+1;
                        echo 'PL';
                    }
                    elseif($holiday != null){
                        $h = $h+1;
                        echo "H";
                    }
                    else{
                        $a = $a+1;
                        echo "A";
                        
                    }
                }
                // if($weekDay == 0){
                //     $wo = $wo+1;
                //     echo "WO";
                // }elseif($leave != null){
                //     $pl = $pl+1;
                //     echo "PL";
                // }
                
                else{
                    if(!empty($data->status)){
                        if($data->status=='1'){
                            $pa = $pa+1;
                            echo "P";
                        }
                        elseif($data->status=='2'){
                            $a = $a+1;
                            echo "A";
                        } 
                        elseif($data->status=='3'){
                            $od = $od+1;
                            echo "OD";
                        }
                        
                        elseif($data->status=='4'){
                            $h = $h+1;
                            echo "H";
                        } 
                       
                        elseif($data->status=='5'){
                            $a = $a+1;
                            echo "A";
                        }
                        elseif($data->status=='6'){
                            $wo = $wo+1;
                            echo "WO";
                        }
                        elseif($data->status=='7'){
                            $l = $l+1;
                            echo "LT";
                        }

                        elseif($data->status=='8'){
                            $pl = $pl+1;
                            echo "PL";
                        }
                       
                        
                        if($data->status=='1' || $data->status=='7' || $data->status=='3'){
                            $p_count++;
                        }
                    }
                    // else{
                    //     $a = $a+1;
                    //     echo "A";
                    // }
                }
                ?>
            </td>
            @endfor

            
            <th>
                {{$p_count + $wo + $h}}
            </th>
            <th>
                {{$pa ?? 0}}
            </th>
            <th>
                {{$l ?? 0}}
            </th>
            <th>
                {{$od ?? 0}}
            </th>
            <th>
                {{$wo ?? 0}}
            </th>
            <th>
                {{$pl ?? 0}}
            </th>
            <th>
                {{$h ?? 0}}
            </th>
            <th>
                {{$a ?? 0}}
            </th>
            
        </tr>
        @endif
        @endforeach

       <!-- role boa and dst start -->
        @foreach($attdence->unique('worker_id') as $key=>$values)
        @if(!in_array($values->worker_role_id, [3,5,6]))
        <tr>
            <td>{{ $values->emp_id }}</td>            
            <td>{{ $values->user_name }}</td>
            <td>-</td>
            <td>{{ $values->user_status == 0 ? "Deactive" : "Active" }}</td>
            <td>{{ !empty($values->sole_id) ? $values->sole_id  : "-" }}</td>
            <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
            <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
            <td> {{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
            <?php
            // $area_id[]=$values->area_id;
            $p_count = 0;
            $A_count = 0;
            $pa=0;
            $od=0;
            $l=0;
            $a=0;
            $wo=0;
            $pl=0;
            $h=0;
            // DB::connection()->enableQueryLog();
           
            // print_r(DB::getQueryLog());
            
            
            ?>
        
            @for($s=0; $s<$cnt; $s++)
            <?php 
             $data = DB::table('attendances')
             ->where('worker_id',$values->worker_id)
             ->where('attendances.worker_role_id', $values->worker_role_id)
             ->where('date', $dateArr[$s])
             ->where('in_time','!=',NULL)
             ->first();
            ?>
            <td>
                <?php
                //leave attendance
                $leave   =  DB::table('leave_logs')->where('user_id',$values->worker_id)
                                    ->where('status',1)
                                    ->where('from_date', $dateArr[$s])
                                    ->first();

                // //holiday attendance
                $userarea  = DB::table('users')->where('id',$values->worker_id)->where('status',1)->first();
                if(!empty($userarea)){
                    $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
                }
                $holiday =Null;
                if(!empty($areastate)){
                    $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
                    ->where('date',$dateArr[$s])     
                    ->first();
                
                }
                // elseif($holiday != null){
                //     $h = $h+1;
                //     echo "H";
                // }
                
                $weekDay = date('w', strtotime($dateArr[$s]));
                if($data ==Null){
                    if($weekDay == 0 || $weekDay == 6){
                        $wo = $wo+1;
                        echo "WO";
                    }elseif($leave != null){
                        $pl = $pl+1;
                        echo 'PL';
                    }
                     
                    elseif($holiday != null){
                        $h = $h+1;
                        echo "H";
                    }

                    else{
                        $a = $a+1;
                        echo "A";
                        
                    }
                }
                else{
                    if(!empty($data->status)){
                        if($data->status=='1'){
                            $pa = $pa+1;
                            echo "P";
                        }

                        elseif($data->status=='2'){
                            $a = $a+1;
                            echo "A";
                        } 
                        
                        elseif($data->status=='3'){
                            $od = $od+1;
                            echo "OD";
                        } 

                        elseif($data->status=='4'){
                            $h = $h+1;
                            echo "H";
                        } 
                       
                        elseif($data->status=='5'){
                            $a = $a+1;
                            echo "A";
                        }
                        elseif($data->status=='6'){
                            $wo = $wo+1;
                            echo "WO";
                        }
                        elseif($data->status=='7'){
                            $l = $l+1;
                            echo "LT";
                        }

                        elseif($data->status=='8'){
                            $pl = $pl+1;
                            echo "PL";
                        }
                        
                        if($data->status=='1' || $data->status=='7' || $data->status=='3'){
                            $p_count++;
                        }
                    }
                }
                ?>
            </td>
            @endfor

            
            <th>
                {{$p_count + $wo + $h}}
            </th>
            <th>
                {{$pa ?? 0}}
            </th>
            <th>
                {{$l ?? 0}}
            </th>
            <th>
                {{$od ?? 0}}
            </th>
            <th>
                {{$wo ?? 0}}
            </th>
            <th>
                {{$pl ?? 0}}
            </th>

             <th>
                {{$h ?? 0}}
            </th>
            <th>
                {{$a ?? 0}}
            </th>

           
        </tr>
        @endif
        @endforeach

        <!-- absent boa and dst start -->
        @foreach($absent_users as $key=>$values)
        <?php  $soleId = DB::table('areas')->where('id',$values->area_id)->first();?>
        @if(!in_array($values->worker_role_id, [3,5,6]))
        <tr>
            <td>{{ $values->emp_id }}</td>            
            <td>{{ $values->user_name }}</td>
            <td>-</td>
            <td>{{ $values->user_status == 0 ? "Deactive" : "Active" }}</td>
            <td>{{ !empty($soleId->name) ? $soleId->name  : "-" }}</td>
            <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
            <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
            <td>{{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
            
            <?php
            // $area_id[]=$values->area_id;
            $p_count = 0;
            $A_count = 0;
            $pa=0;
            $od=0;
            $l=0;
            $a=0;
            $wo=0;
            $pl=0;
            $h=0;
            // DB::connection()->enableQueryLog();
            
            ?>
        
        @for($s=0; $s<$cnt; $s++)
            <?php 
             $data = DB::table('attendances')
             ->where('worker_id',$values->worker_id)
             ->where('attendances.worker_role_id', $values->worker_role_id)
             ->where('date', $dateArr[$s])
             ->where('in_time','!=',NULL)
             ->first();
            ?>
            <td>
                <?php

                //leave attendance
                $leave   =  DB::table('leave_logs')->where('user_id',$values->worker_id)
                                    ->where('status',1)
                                    ->where('from_date', $dateArr[$s])
                                    ->first();
                
                // //holiday attendance
                $userarea  = DB::table('users')->where('id',$values->worker_id)->where('status',1)->first();
                if(!empty($userarea)){
                    $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
                }
                $holiday =Null;
                if(!empty($areastate)){
                    $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
                    ->where('date',$dateArr[$s])     
                    ->first();
                
                }
                
                $weekDay = date('w', strtotime($dateArr[$s]));
                if($data ==Null){
                    if($weekDay == 0 || $weekDay == 6){
                        $wo = $wo+1;
                        echo "WO";
                    }elseif($leave != null){
                        $pl = $pl+1;
                        echo 'PL';
                    }
                    elseif($holiday != null){
                        $h = $h+1;
                        echo "H";
                    }
                    else{
                        $a = $a+1;
                        echo "A";
                        
                    }
                }
                // if($weekDay == 0){
                //     $wo = $wo+1;
                //     echo "WO";
                // }elseif($leave != null){
                //     $pl = $pl+1;
                //     echo "PL";
                // }
                
                else{
                    if(!empty($data->status)){
                        if($data->status=='1'){
                            $pa = $pa+1;
                            echo "P";
                        }
                        elseif($data->status=='2'){
                            $a = $a+1;
                            echo "A";
                        } 
                        elseif($data->status=='3'){
                            $od = $od+1;
                            echo "OD";
                        }
                        
                        elseif($data->status=='4'){
                            $h = $h+1;
                            echo "H";
                        } 
                       
                        elseif($data->status=='5'){
                            $a = $a+1;
                            echo "A";
                        }
                        elseif($data->status=='6'){
                            $wo = $wo+1;
                            echo "WO";
                        }
                        elseif($data->status=='7'){
                            $l = $l+1;
                            echo "LT";
                        }

                        elseif($data->status=='8'){
                            $pl = $pl+1;
                            echo "PL";
                        }
                        
                        if($data->status=='1' || $data->status=='7' || $data->status=='3'){
                            $p_count++;
                        }
                    }
                    // else{
                    //     $a = $a+1;
                    //     echo "A";
                    // }
                }
                ?>
            </td>
            @endfor

            
            <th>
                {{$p_count + $wo + $h}}
            </th>
            <th>
                {{$pa ?? 0}}
            </th>
            <th>
                {{$l ?? 0}}
            </th>
            <th>
                {{$od ?? 0}}
            </th>
            <th>
                {{$wo ?? 0}}
            </th>
            <th>
                {{$pl ?? 0}}
            </th>
            <th>
                {{$h ?? 0}}
            </th>
            <th>
                {{$a ?? 0}}
            </th>
            
        </tr>
        @endif
        @endforeach
    <!-- <tr></tr> -->
     <!-- TSM ROLE START -->
     @foreach($attdence->unique('worker_id') as $key=>$values)
     <?php 
        $rsm_name = Null;
         //tsm rsm name
         $emp  = DB::table('rsm_tsms')->where('tsm_id',$values->user_id)->pluck('rsm_id')->toArray();
         if(!empty($emp)){
             $rsm_name = DB::table('users')->whereIn('id',$emp)->where('role',6)->first();
            
         }

        ?>
        @if($values->worker_role_id=='5')
        <tr>
            <td>{{ $values->emp_id }}</td>            
            <td>{{ $values->user_name }}</td>
            <td>{{ !empty($rsm_name->name) ? $rsm_name->name: "-" }}</td>
            <td>{{ $values->user_status == 0 ? "Deactive" : "Active" }}</td>
            <td>{{ !empty($values->sole_id) ? $values->sole_id  : "-" }}</td>
            <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
            <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
           
            <td> {{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
            
            <?php
            $p_count = 0;
            $A_count = 0;
            $pa=0;
            $od=0;
            $l=0;
            $a=0;
            $wo=0;
            $pl=0;
            $h=0;
            // DB::connection()->enableQueryLog();
            
            ?>
        
        @for($s=0; $s<$cnt; $s++)
            <?php 
             $data = DB::table('attendances')
             ->where('worker_id',$values->worker_id)
             ->where('attendances.worker_role_id', $values->worker_role_id)
             ->where('date', $dateArr[$s])
             ->where('in_time','!=',NULL)
             ->first();
            ?>
            <td>
                <?php

                //leave attendance
                $leave   =  DB::table('leave_logs')->where('user_id',$values->worker_id)
                                    ->where('status',1)
                                    ->where('from_date', $dateArr[$s])
                                    ->first();
                
                // //holiday attendance
                $userarea  = DB::table('users')->where('id',$values->worker_id)->where('status',1)->first();
                if(!empty($userarea)){
                    $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
                }
                $holiday =Null;
                if(!empty($areastate)){
                    $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
                    ->where('date',$dateArr[$s])     
                    ->first();
                
                }
                                 
                $weekDay = date('w', strtotime($dateArr[$s]));
                if($weekDay == 0 || $weekDay == 6){
                    $wo = $wo+1;
                    echo "WO";
                }elseif($leave != null){
                    $pl = $pl+1;
                    echo "PL";
                }
                elseif($holiday != null){
                    $h = $h+1;
                    echo "H";
                }   
                else{
                    if(!empty($data->status)){
                        if($data->status=='1'){
                            $pa = $pa+1;
                            echo "P";
                        }
                        elseif($data->status=='2'){
                            $a = $a+1;
                            echo "A";
                        } 
                        elseif($data->status=='3'){
                            $od = $od+1;
                            echo "OD";
                        } 

                        elseif($data->status=='4'){
                            $h = $h+1;
                            echo "H";
                        } 
                        
                        elseif($data->status=='5'){
                            $a = $a+1;
                            echo "A";
                        }
                        elseif($data->status=='6'){
                            $wo = $wo+1;
                            echo "WO";
                        }
                        elseif($data->status=='7'){
                            $l = $l+1;
                            echo "LT";
                        }

                        elseif($data->status=='8'){
                            $pl = $pl+1;
                            echo "PL";
                        }
                       
                        if($data->status=='1' || $data->status=='7' || $data->status=='3'){
                            $p_count++;
                        }
                    }else{
                        $a = $a+1;
                        echo "A";
                    }
                }
                ?>
            </td>
            @endfor

            
            <th>
                {{$p_count + $wo + $h}}
            </th>
            <th>
                {{$pa ?? 0}}
            </th>
            <th>
                {{$l ?? 0}}
            </th>
            <th>
                {{$od ?? 0}}
            </th>
            <th>
                {{$wo ?? 0}}
            </th>
            <th>
                {{$pl ?? 0}}
            </th>

            <th>
                {{$h ?? 0}}
            </th>
            <th>
                {{$a ?? 0}}
            </th>
            
        </tr>
        @endif
        @endforeach
        
        <!-- absent users start -->
        @foreach($absent_users as $key=>$values)
        <?php  $soleId = DB::table('areas')->where('id',$values->area_id)->first();?>
        <?php 
            $rsm_name = Null;
            //tsm rsm
            $emp  = DB::table('rsm_tsms')->where('tsm_id',$values->id)->pluck('rsm_id')->toArray();
            if(!empty($emp)){
                $rsm_name = DB::table('users')->whereIn('id',$emp)->where('role',6)->first();
            
            }
        ?>
        @if($values->worker_role_id=='5')
        <tr>
            <td>{{ $values->emp_id }}</td>            
            <td>{{ $values->user_name }}</td>
            <td>{{ $rsm_name->name  ?? "-" }}</td>
            <td>{{ $values->user_status == 0 ? "Deactive" : "Active" }}</td>
            <td>{{ !empty($soleId->name) ? $soleId->name  : "-" }}</td>
            <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
            <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
            <td> {{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
            
            <?php
            $p_count = 0;
            $A_count = 0;
            $pa=0;
            $od=0;
            $l=0;
            $a=0;
            $wo=0;
            $pl=0;
            $h=0;
            // DB::connection()->enableQueryLog();
            
            ?>
        
        @for($s=0; $s<$cnt; $s++)
            <?php 
             $data = DB::table('attendances')
             ->where('worker_id',$values->worker_id)
             ->where('attendances.worker_role_id', $values->worker_role_id)
             ->where('date', $dateArr[$s])
             ->where('in_time','!=',NULL)
             ->first();
            ?>
            <td>
                <?php

                //leave attendance
                $leave   =  DB::table('leave_logs')->where('user_id',$values->worker_id)
                                    ->where('status',1)
                                    ->where('from_date', $dateArr[$s])
                                    ->first();
                
                // //holiday attendance
                $userarea  = DB::table('users')->where('id',$values->worker_id)->where('status',1)->first();
                if(!empty($userarea)){
                    $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
                }
                $holiday =Null;
                if(!empty($areastate)){
                    $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
                    ->where('date',$dateArr[$s])     
                    ->first();
                
                }
                                  
                $weekDay = date('w', strtotime($dateArr[$s]));
                if($weekDay == 0 || $weekDay == 6){
                    $wo = $wo+1;
                    echo "WO";
                }elseif($leave != null){
                    $pl = $pl+1;
                    echo "PL";
                }
                elseif($holiday != null){
                    $h = $h+1;
                    echo "H";
                }  
                else{
                    if(!empty($data->status)){
                        if($data->status=='1'){
                            $pa = $pa+1;
                            echo "P";
                        }
                        elseif($data->status=='2'){
                            $a = $a+1;
                            echo "A";
                        } 
                        elseif($data->status=='3'){
                            $od = $od+1;
                            echo "OD";
                        } 

                        elseif($data->status=='4'){
                            $h = $h+1;
                            echo "H";
                        } 
                        
                        elseif($data->status=='5'){
                            $a = $a+1;
                            echo "A";
                        }
                        elseif($data->status=='6'){
                            $wo = $wo+1;
                            echo "WO";
                        }
                        elseif($data->status=='7'){
                            $l = $l+1;
                            echo "LT";
                        }

                        elseif($data->status=='8'){
                            $pl = $pl+1;
                            echo "PL";
                        }
                        
                        if($data->status=='1' || $data->status=='7' || $data->status=='3'){
                            $p_count++;
                        }
                    }else{
                        $a = $a+1;
                        echo "A";
                    }
                }
                ?>
            </td>
            @endfor

            
            <th>
                {{$p_count + $wo + $h}}
            </th>
            <th>
                {{$pa ?? 0}}
            </th>
            <th>
                {{$l ?? 0}}
            </th>
            <th>
                {{$od ?? 0}}
            </th>
            <th>
                {{$wo ?? 0}}
            </th>
            <th>
                {{$pl ?? 0}}
            </th>

            <th>
                {{$h ?? 0}}
            </th>
            <th>
                {{$a ?? 0}}
            </th>
        </tr>
        @endif
        @endforeach
        <!-- <tr></tr> -->
        
        <!-- RSM ROLE START -->
        @foreach($attdence->unique('worker_id') as $key=>$values)
        @if($values->worker_role_id=='6')
            <tr>
                <td>{{ $values->emp_id }}</td>            
                <td>{{ $values->user_name }}</td>
                <td>-</td>
                <td>{{ $values->user_status == 0 ? "Deactive" : "Active" }}</td>
                <td>{{ !empty($values->sole_id) ? $values->sole_id  : "-" }}</td>
                <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
                <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
                <td> {{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
                
                <?php
                $p_count = 0;
                $A_count = 0;
                $pa=0;
                $od=0;
                $l=0;
                $a=0;
                $wo=0;
                $pl=0;
                $h=0;
                // DB::connection()->enableQueryLog();
                
                ?>
            
            @for($s=0; $s<$cnt; $s++)
            <?php 
             $data = DB::table('attendances')
             ->where('worker_id',$values->worker_id)
             ->where('attendances.worker_role_id', $values->worker_role_id)
             ->where('date', $dateArr[$s])
             ->where('in_time','!=',NULL)
             ->first();
            ?>
            <td>
                <?php
                 //leave attendance
                 $leave   =  DB::table('leave_logs')->where('user_id',$values->worker_id)
                                ->where('status',1)
                                ->where('from_date', $dateArr[$s])
                                ->first();
                
                // //holiday attendance
                $userarea  = DB::table('users')->where('id',$values->worker_id)->where('status',1)->first();
                if(!empty($userarea)){
                    $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
                }
                $holiday =Null;
                if(!empty($areastate)){
                    $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
                    ->where('date',$dateArr[$s])     
                    ->first();
                
                }
                                

                $weekDay = date('w', strtotime($dateArr[$s]));
                if($weekDay == 0 || $weekDay == 6){
                    $wo = $wo+1;
                    echo "WO";
                }elseif($leave != null){
                    $pl = $pl+1;
                    echo "PL";
                }
                elseif($holiday != null){
                    $h = $h+1;
                    echo "H";
                } 
                else{
                    if(!empty($data->status)){
                        if($data->status=='1'){
                            $pa = $pa+1;
                            echo "P";
                        }
                        elseif($data->status=='2'){
                            $a = $a+1;
                            echo "A";
                        } 
                        elseif($data->status=='3'){
                            $od = $od+1;
                            echo "OD";
                        } 

                        elseif($data->status=='4'){
                            $h = $h+1;
                            echo "H";
                        } 
                        
                        elseif($data->status=='5'){
                            $a = $a+1;
                            echo "A";
                        }
                        elseif($data->status=='6'){
                            $wo = $wo+1;
                            echo "WO";
                        }
                        elseif($data->status=='7'){
                            $l = $l+1;
                            echo "LT";
                        }

                        elseif($data->status=='8'){
                            $pl = $pl+1;
                            echo "PL";
                        }
                        
                        if($data->status=='1' || $data->status=='7' || $data->status=='3'){
                            $p_count++;
                        }
                    }else{
                        $a = $a+1;
                        echo "A";
                    }
                }
                ?>
            </td>
            @endfor

                
                <th>
                    {{$p_count + $wo + $h}}
                </th>
            <th>
                {{$pa ?? 0}}
            </th>
            <th>
                {{$l ?? 0}}
            </th>
            <th>
                {{$od ?? 0}}
            </th>
            <th>
                {{$wo ?? 0}}
            </th>
            <th>
                {{$pl ?? 0}}
            </th>

            <th>
                {{$h ?? 0}}
            </th>
            <th>
                {{$a ?? 0}}
            </th>
            </tr>
            @endif
        @endforeach

        <!-- absent users start -->
        @foreach($absent_users as $key=>$values)
        <?php  $soleId = DB::table('areas')->where('id',$values->area_id)->first();?>
        @if($values->worker_role_id=='6')
            <tr>
                <td>{{ $values->emp_id }}</td>            
                <td>{{ $values->user_name }}</td>
                <td>-</td>
                <td>{{ $values->user_status == 0 ? "Deactive" : "Active" }}</td>
                <td>{{ !empty($soleId->name) ? $soleId->name  : "-" }}</td>
                <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
                <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
               
                <td> {{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
                
                <?php
                $p_count = 0;
                $A_count = 0;
                $pa=0;
                $od=0;
                $l=0;
                $a=0;
                $wo=0;
                $pl=0;
                $h=0;
                // DB::connection()->enableQueryLog();
                
                ?>
            
            @for($s=0; $s<$cnt; $s++)
            <?php 
           
             $data = DB::table('attendances')
             ->where('worker_id',$values->worker_id)
             ->where('attendances.worker_role_id', $values->worker_role_id)
             ->where('date', $dateArr[$s])
             ->where('in_time','!=',NULL)
             ->where('in_time','!=',NULL)
             ->first();
            ?>
            <td>
                <?php

                //leave attendance
                $leave   =  DB::table('leave_logs')->where('user_id',$values->worker_id)
                                ->where('status',1)
                                ->where('from_date', $dateArr[$s])
                                ->first();
                
                //holiday attendance
                $userarea  = DB::table('users')->where('id',$values->worker_id)->where('status',1)->first();
                if(!empty($userarea)){
                    $areastate = DB::table('areas')->where('id',$userarea->area_id)->first();
                }
                $holiday =Null;
                if(!empty($areastate)){
                    $holiday   = DB::table('holidays')->where('state_id',$areastate->state)
                    ->where('date',$dateArr[$s])     
                    ->first();
                
                }

                $weekDay = date('w', strtotime($dateArr[$s]));
                if($weekDay == 0 || $weekDay == 6){
                    $wo = $wo+1;
                    echo "WO";
                }elseif($leave != null){
                    $pl = $pl+1;
                    echo "PL";
                }
                elseif($holiday != null){
                    $h = $h+1;
                    echo "H";
                } 
                else{
                    if(!empty($data->status)){
                        if($data->status=='1'){
                            $pa = $pa+1;
                            echo "P";
                        }
                        elseif($data->status=='2'){
                            $a = $a+1;
                            echo "A";
                        } 
                        elseif($data->status=='3'){
                            $od = $od+1;
                            echo "OD";
                        }
                        
                        elseif($data->status=='4'){
                            $h = $h+1;
                            echo "H";
                        } 
                       
                        elseif($data->status=='5'){
                            $a = $a+1;
                            echo "A";
                        }
                        elseif($data->status=='6'){
                            $wo = $wo+1;
                            echo "WO";
                        }
                        elseif($data->status=='7'){
                            $l = $l+1;
                            echo "LT";
                        }

                        elseif($data->status=='8'){
                            $pl = $pl+1;
                            echo "PL";
                        }
                        
                        if($data->status=='1' || $data->status=='7' || $data->status=='3'){
                            $p_count++;
                        }
                    }else{
                        $a = $a+1;
                        echo "A";
                    }
                }
                ?>
            </td>
            @endfor

                
                <th>
                    {{$p_count + $wo + $h}}
                </th>
                <th>
                {{$pa ?? 0}}
            </th>
            <th>
                {{$l ?? 0}}
            </th>
            <th>
                {{$od ?? 0}}
            </th>
            <th>
                {{$wo ?? 0}}
            </th>
            <th>
                {{$pl ?? 0}}
            </th>
            <th>
                {{$h ?? 0}}
            </th>
            <th>
                {{$a ?? 0}}
            </th>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>