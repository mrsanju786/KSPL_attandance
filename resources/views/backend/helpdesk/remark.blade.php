@extends('adminlte::page')
<!-- page title -->
@section('title', 'Add Status Remark' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Add Status Remark</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
    <a href="{{url('helpdesk')}}" class="btn btn-primary" style="width: 100px;">Back</a>
        <div class="card-header">
            
            <h3 class="card-title">Add Remark</h3>
            
        </div>
           
        </div>
        <form action ="{{url('helpdesk/remark')}}/{{base64_encode($data->id)}}" method ="POST">
        {{ Form::hidden('id', $data->id) }}
        @csrf
        <div class="container">
       
        
            <div class="form-group">
            <label for="usr">Remark:</label>
            <input type="text" name ="remark" class="form-control" required>
            </div>
            
       
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
