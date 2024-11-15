<?php

use Illuminate\Support\Facades\DB;

$worker_ids = DB::select("SELECT DISTINCT(worker_id) FROM attendances");
// $myBagArray = (array)$worker_ids;

// dd($worker_ids);

?>



<table>
    <thead>
        <tr>



            <th>Emp Id</th>
            <th>Emp Name</th>
            <th>worker_id</th>
            <th>Work Device Id</th>
            {{--<th>Role Name</th>--}}

            @foreach($attdence as $value)
            <th>{{$value->date}}</th>
            @endforeach
            <th>Payable Days</th>

        </tr>
    </thead>
    <tbody>
        <?php
        // print_r($attdence[0]['worker_id']);
        // die;
        foreach ($attdence as $value) {

            $array[] = $value->worker_id;
        }

        // $array = array(1533, 2343, 3350);
        foreach ($worker_ids as $key => $value) {
            // echo "jldjfs";
            // echo $value->worker_id;
            // dd($key);
            if (in_array($value->worker_id, $array)) {
                //         echo 'hudsfh';
                //     }

                // }
                // die();
                // $i=0;
                // $a=array_unique($attdence);
        ?>
                @foreach($a as $key=>$value)
                <?php
                if(in_array($value->worker_id, $array)) {
                
                ?>
                <tr>

                    <td>{{ $value->worker_id }}</td>


                    <td>{{ $value->user_name }}</td>


                    <td>{{ !empty($value->in_location_id) ? $value->in_location_id  : "-" }}</td>


                    <td>{{ !empty($value->worker_device_id) ? $value->worker_device_id  : "-" }}</td>

                    @foreach($attdence->unique('in_time') as $value)
                    <th>@if(!empty($value->in_time))
                        {{ "P"}}
                        @else
                        {{"A"}}
                        @endif
                    </th>
                    @endforeach
                    <!-- <td>@if(!empty($countdays))
                {{$countdays}}
            @endif</td> -->
                </tr>
                <?php
                }
                ?>
                @endforeach
        <?php
            }
        }
        ?>


    </tbody>
</table>