@extends('adminlte::page')
<!-- page title -->
@section('title', 'Message | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Message</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Message</h3>
        </div>

        {{ Form::open(array('method' => 'POST','autocomplete' => 'off', 'files' => true)) }}

        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Select Email</strong>
                </div>
                
                <div class="col-sm-10 col-content">
                    <select class="form-control" name="phone">
                    <option>Select User</option>
                    @foreach($data as $mail)
                        <option value="{{$mail->mobile_number}}">{{$mail->name}}</option>
                    @endforeach
                    </select>
                </div>
                
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Email</strong>
                </div>
                <div class="col-sm-10 col-content">
                    <textarea class="form-control" name="message"></textarea>
                </div>
            </div>
            
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">Send</button>
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
    <script src="{{ asset('js/backend/profile/form.js') }}"></script>
@stop
