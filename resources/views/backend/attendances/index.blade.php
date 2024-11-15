{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Attendances  | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Attendances</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Export Attendance Monthly
            <br>
             <br>
             <div>
             
             <h3 class="card-title" style="padding-left: 20px;color:red;"> * If you are selecting a single date you need to put start and end date as same.
             
             </div>
            </h3>
        </div>

        <div class="card-body">

            <!-- <h6><b>Export Attendance Monthly</b>
        
            </h6> -->
            <!-- <form action="{{ url('excel_export')}}" method="POST" class="mb-20">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="monthly">Select Month</label>
                            <div class="input-group">
                                <input type="text" name="monthly" class="form-control" id="monthly" placeholder="Monthly" autocomplete="off">
                                <div class="input-group-append" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" id="role_filter" style="
                    align-items: center;">
                        <div>
                        <label for="role" class="m-0">Roles</label>
                            <select name="role" id="role" class="form-control" >
                                <option value="">All</option>
                                @foreach ($roles as $value )
                                 <option value="{{$value->id}}">{{$value->display_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4" style="display: flex;
                    align-items: center;">
                        <div style="margin-top: 16px">
                            <input type="submit" value="Export" class="btn btn-primary">
                        </div>
                    </div>
                </div>
            </form>

            <hr> -->

            <!-- Filtering -->
            <!-- <div class="row">
                <div class="col-md-6"> -->
                    <!-- <div id="date_filter" class="form-inline"> -->
                        <div class="row" id="date_filter">
                            <div class="col-md-4">
                                <!-- <div class="form-group  mb-2"> -->
                                    <label for="to">Select Month</label>
                                    <div class="input-group">
                                        <input type="text" name="monthly" class="form-control" id="monthly" placeholder="Monthly" autocomplete="off">
                                        <div class="input-group-append" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                <!-- </div> -->
                            </div>
                            <div class="col-md-4">
                                <label for="role">Roles</label>
                                <select name="roles" id="roles" class="form-control" >
                                    <option value="1">All</option>
                                    @foreach ($roles as $value )
                                    <option value="{{$value->id}}">{{$value->display_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                            <input type="hidden" name="role" id="role" value="3">
                            <div class="form-group mx-sm-3 mb-2">
                                <input type="submit" name="filter" id="filter" value="Submit" class="btn btn-primary" style="margin-top: 30px;">
                            </div>
                        </div>
                        </div>
                    <!-- </div> -->
                <!-- </div>
            </div> -->
            <span style="color:red;" id="textWarning"></span>
            <hr>
            <!-- Filtering -->

            <div class="table-responsive table-result" style="display:none">
                {!! $html->table(['class' => 'table table-hover']) !!}
            </div>
        </div>
    </div>

    
@stop

@section('css')

<style>
    .hoverable-image {
    position: absolute;
}

.hoverable-image > div {
    position: absolute;
    left: 40%;
    top: 40%;
}

.hoverable-image:not(:hover) > div {
    display: none;
}
</style>    
    <link href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables-plugins/buttons/css/buttons.bootstrap4.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/css/bootstrap-datepicker.css') }}">
@stop

@section('js')
    <!--Data tables-->
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/jszip/jszip.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/pdfmake/pdfmake.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/pdfmake/vfs_fonts.js') }}"></script>
    {{--Button--}}
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/dataTables.buttons.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.colVis.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.html5.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.print.js') }}"></script>
    {!! $html->scripts() !!}
    {{--Datepicker--}}
    <script src="{{ asset('vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/main_index.js'). '?v=' . rand(99999,999999) }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();
            // $('#min, #max ,#role, #monthly').change(function () {
            //     window.LaravelDataTables["dataTableBuilder"].draw();
            // });
            $('#filter').click(function () {
                var monthly     = $('#monthly').val();
                if((monthly.length == 0)){
                    $('#monthly').addClass('is-invalid');
                    $('#textWarning').html('*Select Month.');
                    return false;
                }else{
                    $('#monthly').removeClass('is-invalid');
                    $('#textWarning').remove();
                    window.LaravelDataTables["dataTableBuilder"].draw();
                    $('.table-result').show();
                }
            });

            $('#min').datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: 'TRUE',
                autoclose: true,
                changeMonth: true,
                changeYear: true,
                onSelect: function () {
                    window.LaravelDataTables["dataTableBuilder"].draw();
                },
            });

            $("#max").datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: 'TRUE',
                autoclose: true,
                changeMonth: true,
                changeYear: true,
                onSelect: function () {
                    window.LaravelDataTables["dataTableBuilder"].draw();
                },
            });

            $('#monthly').datepicker({
                format: 'yyyy-mm',
                startView: "months", 
                minViewMode: "months",
                // todayHighlight: 'TRUE',
                // changeMonth: true,
                // changeYear: true,
                autoclose: true,
                onSelect: function () {
                    window.LaravelDataTables["dataTableBuilder"].draw();
                },
            });

            $('#roles').change(function(){

            var put_role = $(this).val();
            $('#role').val(put_role);
            });
        });
    </script>
@stop
