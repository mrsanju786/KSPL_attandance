@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create Leave Balance' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Leave Balance</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add</h3>
        </div>

        {{ Form::open(array('url' => route('leavebalance.create'), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
       @csrf
        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Employee ID<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('user_id', ' ', array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Employee Id.
                    </small>
                </div>
            </div>

            <!-- <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Name<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('name', ' ', array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> User name.
                    </small>
                </div>
            </div> -->

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Paid Leave<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('paid_leaves', ' ', array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Paid Leave.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Casual Leave<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('casual_leaves', ' ', array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Casual Leave.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Sick Leave<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('sick_leaves', ' ', array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Sick Leave.
                    </small>
                </div>
            </div>

            {{-- <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Leave Balance<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('leave_balance', ' ', array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Leave Balance.
                    </small>
                </div>
            </div> --}}
            {{-- <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Month<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                   <select class="form-control" name="month">
                     <option value ="">Select Month</option>
                     <option value ="January">January</option>
                     <option value ="Febaury">Febaury</option>
                     <option value ="March">March</option>
                     <option value ="April">April</option>
                     <option value ="May">May</option>
                     <option value ="June">June</option>
                     <option value ="July">July</option>
                     <option value ="August">August</option>
                     <option value ="September">September</option>
                     <option value ="October">October</option>
                     <option value ="November">November</option>
                     <option value ="December">December</option>

                   </select>
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Month.
                    </small>
                </div>
            </div> --}}
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Year<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                <select class="form-control" name="year">
                     <option value ="">Select Year</option>
                     <option value ="2023">2023</option>
                     <option value ="2024">2024</option>
                     <option value ="2025">2025</option>
                     <option value ="2026">2026</option>
                     <option value ="2027">2027</option>
                     <option value ="2028">2028</option>
                     <option value ="2029">2029</option>
                     <option value ="2030">2030</option>
                   
                   </select>
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>Year.
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



