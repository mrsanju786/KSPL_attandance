@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Designation ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Designation</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add or Update</h3>
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }} 
        {{ Form::hidden('id', $data->id,)}}
        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Designation<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-5 col-content">
                    {{ Form::text('designation', $data->name, array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Designation.
                    </small>
                </div>
            </div>
            @if (Request::is('designation/add'))
                
{{-- 
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Company Name<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-5 col-content ">
                    <select  name="company" class="form-control"> 
                        @foreach ($company as $key => $value) 
                          <option value="{{ $key }}"
                          >  
                              {{ $value }}  
                          </option> 
                        @endforeach     
                      </select>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Branch Name<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    <select name="branch"  class="form-control">
                        @foreach ($branch as $key => $value)
                        <option value="{{ $key}}"
                        {{($key == $data->Branch_id)? "selected" : ""}}>
                    {{$value}}
                    </option>        
                        @endforeach
                    </select>
                </div>
            </div> --}}
            
            @endif 
 
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-8 text-center top20">
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
@stop

@section('js')
    <script>var typePage = "{{ $data->page_type }}";</script>
    <script src="{{ asset('js/backend/users/form.js'). '?v=' . rand(99999,999999) }}"></script>
    <script>
//         $.ajaxSetup({
//     headers: {
//         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//     }
// });

 


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
