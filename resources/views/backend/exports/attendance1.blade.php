<table>
    <thead>
        <tr>



            <th>Emp Code</th>
            <th>Emp Name</th>
            <th>Branch Id</th>
            <th>Location</th>
            <th>Work Device Id</th>
            <th>In Time</th>
            {{--<th>Role Name</th>--}}

            @foreach($attdence->unique('date') as $value)
            <th>{{$value->date}}</th>
            @endforeach
            <th>Payable Days</th>

        </tr>
    </thead>
    <tbody>
        <?php

        // $area_id = []; 
        $dateArr = array_values($attdence->unique('date')->toArray());
        $cnt = count($attdence->unique('date')); ?>
        @foreach($attdence->unique('worker_id') as $key=>$values)
        @if(!in_array($values->worker_role_id, [5,6]))
        <tr>
            <td>{{ $values->emp_id }}</td>            
            <td>{{ $values->user_name }}</td>
            <td>{{ !empty($values->area_id) ? $values->area_id  : "-" }}</td>
            <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
            <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
            <td>{{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
            
            <?php
            // $area_id[]=$values->area_id;
            $p_count = 0;
            $A_count = 0;
            // DB::connection()->enableQueryLog();
            $data = DB::table('attendances')
            ->where('worker_id',$values->worker_id)
            ->where('attendances.worker_role_id', $values->worker_role_id)
            ->where('in_time','!=',NULL)
            ->get();
            // print_r(DB::getQueryLog());
            $arr=[];
            foreach($data as $key=>$d){
                $arr[]= $data[$key]->date;
            }
            // print_r($arr);
            // die;
            $t=0;
            
            ?>
        
            @for($s=0; $s<$cnt; $s++)
            
            <td>@if($t < count($data))
                @if(in_array($dateArr[$s]->date, $arr))
                    @if($values->status == 3)
                        {{"OT"}}
                    @else
                        {{"P"}}
                    @endif
                
                <?php
                
                $t++;
                $p_count++;
                ?>
                @else
                {{"A"}}

                @endif
                @else
                {{"A"}}
                @endif
            </td>
            @endfor

            
            <th>
                {{$p_count}}
            </th>
        </tr>
        @endif
        @endforeach
    <tr></tr>
    <!-- TSM ROLE START -->
    @foreach($attdence->unique('worker_id') as $key=>$values)
        @if($values->worker_role_id=='5')
        <tr>
            <td>{{ $values->emp_id }}</td>            
            <td>{{ $values->user_name }}</td>
            <td>{{ !empty($values->area_id) ? $values->area_id  : "-" }}</td>
            <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
            <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
            <td>{{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
            
            <?php
            $p_count = 0;
            $A_count = 0;
            // DB::connection()->enableQueryLog();
            $data = DB::table('attendances')
            ->where('worker_id',$values->worker_id)
            ->where('attendances.worker_role_id', $values->worker_role_id)
            ->where('in_time','!=',NULL)
            ->get();
            // print_r(DB::getQueryLog());
            $arr=[];
            foreach($data as $key=>$d){
                $arr[]= $data[$key]->date;
            }
            // print_r($arr);
            // die;
            $t=0;
            
            ?>
        
            @for($s=0; $s<$cnt; $s++)
            
            <td>@if($t < count($data))
                @if(in_array($dateArr[$s]->date, $arr))
                {{ "P"}}
                
                <?php
                
                $t++;
                $p_count++;
                ?>
                @else
                {{"A"}}

                @endif
                @else
                {{"A"}}
                @endif
            </td>
            @endfor

            
            <th>
                {{$p_count}}
            </th>
        </tr>
        @endif
        @endforeach
        <tr></tr>
        
        <!-- RSM ROLE START -->
        @foreach($attdence->unique('worker_id') as $key=>$values)
        @if($values->worker_role_id=='6')
            <tr>
                <td>{{ $values->emp_id }}</td>            
                <td>{{ $values->user_name }}</td>
                <td>{{ !empty($values->area_id) ? $values->area_id  : "-" }}</td>
                <td>{{ !empty($values->address) ? $values->address  : "-" }}</td>
                <td>{{ !empty($values->worker_device_id) ? $values->worker_device_id  : "-" }}</td>
                <td>{{ !empty($values->in_time) ? $values->in_time  : "-" }}</td>
                
                <?php
                $p_count = 0;
                $A_count = 0;
                // DB::connection()->enableQueryLog();
                $data = DB::table('attendances')
                ->where('worker_id',$values->worker_id)
                ->where('attendances.worker_role_id', $values->worker_role_id)
                ->where('in_time','!=',NULL)
                ->get();
                // print_r(DB::getQueryLog());
                $arr=[];
                foreach($data as $key=>$d){
                    $arr[]= $data[$key]->date;
                }
                // print_r($arr);
                // die;
                $t=0;
                
                ?>
            
                @for($s=0; $s<$cnt; $s++)
                
                <td>@if($t < count($data))
                    @if(in_array($dateArr[$s]->date, $arr))
                    {{ "P"}}
                    
                    <?php
                    
                    $t++;
                    $p_count++;
                    ?>
                    @else
                    {{"A"}}

                    @endif
                    @else
                    {{"A"}}
                    @endif
                </td>
                @endfor

                
                <th>
                    {{$p_count}}
                </th>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>