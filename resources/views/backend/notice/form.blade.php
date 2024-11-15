@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Notice ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Notice</h1>
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
                    <strong class="field-title">Title<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::text('title', $data->title, array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Title.
                    </small>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Message<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-6 col-content">
                    {{ Form::textarea('message', $data->message, array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Message.
                    </small>
                </div>
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

@section('css')
    <!-- <link href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables-plugins/buttons/css/buttons.bootstrap4.css') }}" rel="stylesheet"> -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/css/bootstrap-datepicker.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script>var typePage = "{{ $data->page_type }}";</script>
<script src="{{ asset('js/backend/users/form.js'). '?v=' . rand(99999,999999) }}"></script>
    
@stop
