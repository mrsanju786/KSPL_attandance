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
            <th>Out Time</th>

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

      
        $dateArr = $dates;
        $cnt = count($dates); ?>
      
        @foreach($absent_users as $key=>$values)
       
        <tr>
            <td>{{ $values->emp_id }}</td>            
            <td>{{ $values->user_name }}</td>
            <td>{{ $values->rsm_name }}</td>            
            <td>{{ $values->user_status }}</td>            
            <td>{{ $values->soleId }}</td>            
            <td>{{ $values->address }}</td>            
            <td>{{ $values->worker_device_id }}</td>            
            <td>{{ $values->in_time }}</td>               
            <td>{{ $values->out_time }}</td>               

        @foreach($values->date_att as $key=>$date_att)
        <td>{{ $date_att }}</td>  
        @endforeach

         <td>{{ $values->payable_days }}</td>            
            <td>{{ $values->present }}</td>            
            <td>{{ $values->late }}</td>            
            <td>{{ $values->out_door }}</td>            
            <td>{{ $values->week_off }}</td>            
            <td>{{ $values->leave }}</td>            
            <td>{{ $values->holiday }}</td>            
            <td>{{ $values->absent }}</td>     
            
        </tr>
        @endforeach
        
    </tbody>
</table>