@extends('adminlte::page')
<!-- page title -->
@section('title', 'Update Leave Balance' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Leave Balance</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Update</h3>
        </div>

        {{ Form::open(array('url' => route('leavebalance.update'), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
       @csrf
       {{ Form::hidden('id', $data->id, array('id' => 'user_id')) }}
        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Employee ID<span style="color:red;">*</span></strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('user_id', $user->emp_id, array('class' => 'form-control', 'required')) }}
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
                    {{ Form::text('paid_leaves', $data->paid_leaves, array('class' => 'form-control', 'required')) }}
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
                    {{ Form::text('casual_leaves', $data->casual_leaves, array('class' => 'form-control', 'required')) }}
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
                    {{ Form::text('sick_leaves', $data->sick_leaves, array('class' => 'form-control', 'required')) }}
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
                    {{ Form::text('leave_balance', $data->leave_balance, array('class' => 'form-control', 'required')) }}
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
                     <option value ="January" {{$data->month == 'January'  ? 'selected' : ''}}>January</option>
                     <option value ="Febaury" {{$data->month == 'Febaury'  ? 'selected' : ''}}>Febaury</option>
                     <option value ="March" {{$data->month == 'March'  ? 'selected' : ''}}>March</option>
                     <option value ="April" {{$data->month == 'April'  ? 'selected' : ''}}>April</option>
                     <option value ="May" {{$data->month == 'May'  ? 'selected' : ''}}>May</option>
                     <option value ="June" {{$data->month == 'June'  ? 'selected' : ''}}>June</option>
                     <option value ="July" {{$data->month == 'July'  ? 'selected' : ''}}>July</option>
                     <option value ="August" {{$data->month == 'August'  ? 'selected' : ''}}>August</option>
                     <option value ="September" {{$data->month == 'September'  ? 'selected' : ''}}>September</option>
                     <option value ="October" {{$data->month == 'October'  ? 'selected' : ''}}>October</option>
                     <option value ="November" {{$data->month == 'November'  ? 'selected' : ''}}>November</option>
                     <option value ="December" {{$data->month == 'December'  ? 'selected' : ''}}>December</option>

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
                     <option value ="2023" {{$data->year == '2023'  ? 'selected' : ''}}>2023</option>
                     <option value ="2024" {{$data->year == '2024'  ? 'selected' : ''}}>2024</option>
                     <option value ="2025" {{$data->year == '2025'  ? 'selected' : ''}}>2025</option>
                     <option value ="2026" {{$data->year == '2026'  ? 'selected' : ''}}>2026</option>
                     <option value ="2027" {{$data->year == '2027'  ? 'selected' : ''}}>2027</option>
                     <option value ="2028" {{$data->year == '2028'  ? 'selected' : ''}}>2028</option>
                     <option value ="2029" {{$data->year == '2029'  ? 'selected' : ''}}>2029</option>
                     <option value ="2030" {{$data->year == '2030'  ? 'selected' : ''}}>2030</option>
                   
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



