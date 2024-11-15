@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Holiday ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Holiday</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add or Update</h3>
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
        {{ Form::hidden('id', $data->id, array('id' => 'user_id')) }}
        @csrf
        <div class="card-body">

        <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Date<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('date', $data->date, array('class' => 'form-control', 'required','id'=>"max")) }}
                    <!-- <div class="input-group-append" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div> -->
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Holiday Date.
                    </small>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Name<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('name', $data->name, array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Holiday Name.
                    </small>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">State<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-6 col-content">
                   
                    <select name ="state_id" class="form-control" required>
                        @foreach($state as $value)
                   
                        <option value="{{$value->id}}"> {{$value->name}}</option>
                       
                        @endforeach
                       
                    </select> 
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>State Name.
                    </small>
                </div>
            </div>
            
            <!-- <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">State</strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::select('area_id', $area_id, $data->area_id, array('id' => 'area_id', 'class' => 'form-control select2', 'placeholder'=>'Select Option','required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Area
                    </small>
                </div>
            </div> -->

            
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">{{ $data->button_text }}</button>
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
    <!-- <link href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables-plugins/buttons/css/buttons.bootstrap4.css') }}" rel="stylesheet"> -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/css/bootstrap-datepicker.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
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
$("#max").datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: 'TRUE',
                autoclose: true,
                changeMonth: true,
                changeYear: true,
                // onSelect: function () {
                //     window.LaravelDataTables["dataTableBuilder"].draw();
                // },
            });
    </script>
@stop
