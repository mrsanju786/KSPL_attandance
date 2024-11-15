@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Users ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Users</h1>
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

        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Employee ID<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('emp_id', $data->emp_id, array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Employee Id.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Name<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('name', $data->name, array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> User name.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Mobile Number<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('mobile_number', $data->mobile_number, array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Mobile Number.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Email</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::email('email',$data->email, array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> User email, this email for login.
                    </small>
                </div>
            </div>

            <div id="form-password" class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Password</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::password('password', array('id' => 'password', 'class' => 'form-control', 'autocomplete' => 'new-password')) }}
                    @if($data->page_type === 'edit')
                        <small id="passwordHelpBlock" class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i> Leave it blank if you don't want to change
                        </small>
                    @else
                        <small class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i> User password, this password for login.
                        </small>
                    @endif
                    <label class="reset-field-password" for="show-password"><input id="show-password" type="checkbox" name="show-password" value="1"> Show Password</label>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Blood Group</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('blood_group',$data->blood_group, array('class' => 'form-control')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Blood Group
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Emergency Contact </strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('emergency_contact',$data->emergency_contact, array('class' => 'form-control')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Emergency Contact
                    </small>
                </div>
            </div>

            {{--  image  --}}
            <div id="form-image" class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Image</strong>
                </div>
                <div class="col-sm-10 col-content">
                    <input class="custom-file-input" name="image" type="file"
                           accept="image/gif, image/jpeg,image/jpg,image/png" >
                    {{-- <input class="custom-file-input" name="image" type="file"
                           accept="image/gif, image/jpeg,image/jpg,image/png" data-max-width="800"
                           data-max-height="400"> --}}
                    <label class="custom-file-label" for="customFile">Choose file</label>
                    <span
                        class="image-upload-label"><i class="fa fa-question-circle" aria-hidden="true"></i> Please upload the image (Recommended max 2MB)</span>
                    <div class="image-preview-area">
                        <div id="image_preview" class="image-preview">
                            @if ($data->page_type == 'edit')
                                <img src="{{ asset('uploads/'.$data->image) }}" width="160" title="image"
                                     class="img-circle elevation-2">
                            @else
                                <img src="{{ asset('img/default-user.png') }}" width="160" title="image"
                                     class="img-circle elevation-2">
                            @endif
                        </div>
                        {{-- only image has main image, add css class "show" --}}
                        <p class="delete-image-preview @if ($data->image != null && $data->image != 'default-user.png') show @endif"
                           onclick="deleteImagePreview(this);"><i class="fa fa-window-close"></i></p>
                        {{-- delete flag for already uploaded image in the server --}}
                        <input name="image_delete" type="hidden">
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Home Latitude 1 </strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('home_latitude1',$data->home_latitude1, array('class' => 'form-control')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Home Latitude 1
                    </small>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Home Longitude 1 </strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('home_longitude1',$data->home_longitude1, array('class' => 'form-control')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Home Longitude 1
                    </small>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Home Latitude 2 </strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('home_latitude2',$data->home_latitude2, array('class' => 'form-control')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Home Latitude 2
                    </small>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Home Longitude 2 </strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('home_longitude2',$data->home_longitude2, array('class' => 'form-control')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Home Longitude 2
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Role</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::select('role', $role, $data->role, array('id' => 'role', 'class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> User role.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Designation</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::select('designation', $designation, $data->designation, array('id' => 'designation', 'class' => 'form-control', 'placeholder'=>'Select Option', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Designation
                    </small>
                </div>
            </div>

            <div class="form-group row" id="rsm_div">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">RSM</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::select('rsm', $rsm, $data->rsm, array('id' => 'rsm', 'class' => 'form-control select2', 'placeholder'=>'Select Option')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>RSM
                    </small>
                </div>
            </div>

            <div class="form-group row" id="tsm_rsm_div">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">TSM/RSM</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::select('tsm_rsm', $tsm_rsm, $data->tsm_rsm, array('id' => 'tsm_rsm', 'class' => 'form-control select2', 'placeholder'=>'Select Option')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>TSM/RSM
                    </small>
                </div>
            </div>

            {{-- area dropdown --}}
            <!-- <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Deployed Area/Sole ID</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::select('area_id', $area_id, $data->area_id, array('id' => 'area_id', 'class' => 'form-control select2', 'placeholder'=>'Select Option','required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Area
                    </small>
                </div>
            </div> -->

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Deployed Area/Sole ID</strong>
                </div>
                <div class="col-sm-10 col-content">
                   <select class="form-control select2" name = "area_id" id="area_id" required>
                    @foreach($area as $value)
                    <option value ="{{$value->id}}" {{$data->area_id == $value->id  ? 'selected' : ''}}>{{$value->address}}({{$value->name}})</option>
                    @endforeach
                   <select>
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Deployed Area/Sole ID
                    </small>
                </div>
            </div>


            {{-- multiplearea dropdown --}}
            <!-- <div class="form-group row" id="multiple_area">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Mapped Area/Sole ID</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::select('area_id_multiple[]', $area_id, $data->area_id_multiple, array('id' => 'area_id_multiple', 'class' => 'form-control select2', 'multiple')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Multiple Area
                    </small>
                </div>
            </div> -->
          
            <div class="form-group row" id="multiple_area">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Mapped Area/Sole ID</strong>
                </div>
                <div class="col-sm-10 col-content">
               
                    <select class="form-control select2" name = "area_id_multiple[]" id="area_id_multiple" multiple >
                    <!-- <option value ="" disabled>Select Mapped Area</option> -->
                    
                    @foreach($area as $value)
                    
                    <option value ="{{$value->id}}" {{in_array($value->id, $tsm_area->toArray())  ? 'selected' : ''}}>{{$value->address}}({{$value->name}})</option>
                    @endforeach
                   <select>
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Mapped Area/Sole ID
                    </small>
                </div>
            </div>

            

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
