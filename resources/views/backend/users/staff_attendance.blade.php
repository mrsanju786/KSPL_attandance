@extends('adminlte::page')
<!-- page title -->
@section('title', 'Add Staff Attendance' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Add Staff Attendance</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
    <a href="{{url('users/list')}}" class="btn btn-primary" style="width: 100px;">Back</a>
        <div class="card-header">
            
            <h3 class="card-title">Add Attendance</h3>
             <br>
             <br>
             <div>
             
             <h3 class="card-title" style="padding-left: 20px;color:red;"> * It is mandatory to fill all date fields..<br>
              * Any change in attendance should be done only after confirmation is given by the employee through mail.</div>
             </div>
            <br>
            <br>
            <div>
            <h3 class="card-title" style="padding-left: 20px;">Name     : {{$data->name ?? "-"}}</h3>
            <h3 class="card-title" style="padding-left: 20px;">Sole Id  : {{$area->name ?? "-"}}</h3>
            <h3 class="card-title" style="padding-left: 20px;">Location : {{$area->address ?? "-"}}</h3>
            
            </div>
           
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
        {{ Form::hidden('id', $data->id, array('id' => 'user_id')) }}
        @csrf
        <input type="hidden" name = "worker_id" value="{{$data->id}}">
        <input type="hidden" name = "role" value="{{$data->role}}">
        <input type="hidden" name = "area_id" value="{{$data->area_id}}">
        <input type="hidden" name = "device_id" value="{{$data->device_id}}">
        <?php
            $date1 = date('d-m-Y', strtotime($data->deactivated_at));
            $date2 = date('d-m-Y');

           
            function getBetweenDates($startDate, $endDate) {
                $rangArray = [];
             
                $startDate = strtotime($startDate);
                $endDate = strtotime($endDate);
             
                for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
                    $date = date('Y-m-d', $currentDate);
                    $rangArray[] = $date;
                }
             
                return $rangArray;
            }
            $dates = getBetweenDates($date1, $date2);
           
        ?>
       
        <div class="card-body">
        <div class="row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Date</strong>
            </div>
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Status</strong>
            </div>
        </div>    
          
          @foreach($dates as $key =>$value)
          @php 
         
            $date    = date('d-m-Y',strtotime($value));
            $weekDay = date('w', strtotime($value));
            $userArea   = $data->area_id;
            $area       = App\Models\Area::where('id',$userArea)->first();
            $holiday    = App\Models\Holiday::where('state_id',$area->state)->where('date',$value)->first();
           
          @endphp

        
           <div class="row">
          
                <div class="form-group row">
                   
                    <div class="col-sm-10 col-content">
                        {{ Form::text('date[]',$date , array('class' => 'form-control','readonly')) }}
                        <!-- <small class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i>Date.
                        </small> -->
                    </div>
                </div>
                @if($holiday != null)
                <div class="form-group row">
                    
                    <?php $status1 = array('4'=>'H');?>
                    <div class="col-sm-12 col-content">
                        {{ Form::select('status[]', $status1,'', array('id' => 'status', 'class' => 'form-control' ,'readonly'=>'readonly')) }}
                        <!-- <small class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i> Status.
                        </small> -->
                    </div>
                </div>
                @elseif($weekDay == 0 || $weekDay == 6)
                <div class="form-group row">
                    
                    <?php $status1 = array('6'=>'wo');?>
                    <div class="col-sm-12 col-content">
                        {{ Form::select('status[]', $status1,'', array('id' => 'status', 'class' => 'form-control' ,'readonly'=>'readonly')) }}
                        <!-- <small class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i> Status.
                        </small> -->
                    </div>
                </div>
                @else
                <div class="form-group row">
                    
                    <?php $status = array(''=>"Select Status",'1'=>'Present','5'=>'Absent', '7'=>'Late','3'=>"Out Door",'8'=>"Paid Leave");?>
                    <div class="col-sm-12 col-content">
                        {{ Form::select('status[]', $status,'', array('id' => 'status', 'class' => 'form-control', 'required')) }}
                        <!-- <small class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i> Status.
                        </small> -->
                    </div>
                </div>
                @endif
                
           </div>
         @endforeach
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>

    <!-- /.card -->
    </div>
    <!-- /.row -->
    <!-- /.content -->
@stop

@section('css')
@stop

@section('js')
    <script>var typePage = "{{ $data->page_type }}";</script>
    <script src="{{ asset('js/backend/users/form.js'). '?v=' . rand(99999,999999) }}"></script>
    <script>
        $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$('#tsm_rsm').on('change',function(e){
    var id=$(this).val();
    $('#area_id').empty();
    if(id){
        $.ajax({
                 url:"{{url('users/tsm_rsm_area_list/')}}",
                type:"POST",
                data:{
                    id:id,
                    _token: "{{ csrf_token() }}",
                },
                dataType: "json",
                success:function(response){
                     $.each(response,function(key,value){      
                        $('#area_id').append('<option value="'+ value.id +'">'+ value.address +'</option>');
                        $('#area_id').select2();
                   });
                }
        });
    }
    else{
        $('#area_id').select2();  
    }
   
})


//     var  edit_id=$('#tsm_rsm').val();
//     $('#area_id').empty();
//     if(edit_id){
//         $.ajax({
//                  url:"{{url('users/tsm_rsm_area_list/')}}",
//                 type:"POST",
//                 data:{
//                     id:edit_id,
//                     _token: "{{ csrf_token() }}",
//                 },
//                 dataType: "json",
//                 success:function(response){
//                      $('#area_id').select2();
//                      $('#area_id').append('<option value="">Select Option</option>');
//                      $.each(response,function(key,value){      
//                         $('#area_id').append('<option value="'+ value.id +'">'+ value.address +'</option>');
//                         $('#area_id').select2();
//                    });
//                 }
//         });
//     }
//     else{
//         $('#area_id').select2(); 
//     }

    </script>
@stop
