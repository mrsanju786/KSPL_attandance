{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Reports | ' . Config::get('adminlte.title'))

@section('content_header')
<h1>Reports</h1>
@stop

@section('content')
{{--Show message if any--}}
@include('layouts.flash-message')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Attendance List</h3>
    </div>

    <div class="card-body">
        <!-- Filtering -->
        <div class="row">
            <div class="col">
                <div id="date_filter">
                    <div class="row">
                        <div class="col">
                            <div class="form-group mb-2">
                                <label for="from"></label>
                                <div class="input-group">
                                    <input type="text" name="dateFrom" class="form-control" id="min" placeholder="From Date" autocomplete="off">
                                    <div class="input-group-append" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group mx-sm-3 mb-2">
                                <label for="to"></label>
                                <div class="input-group">
                                    <input type="text" name="dateTo" class="form-control" id="max" placeholder="To Date" autocomplete="off">
                                    <div class="input-group-append" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group mx-sm-3 mb-2">
                                <!-- <label for="monthly">Select Month</label>
                                <div class="input-group">
                                    <input type="text" name="monthly" class="form-control" id="monthly" placeholder="Monthly" autocomplete="off">
                                    <div class="input-group-append" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div> -->
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group mx-sm-3 mb-2">
                                <!-- <label for="yearly">Select Year</label>
                                <div class="input-group">
                                    <input type="text" name="yearly" class="form-control" id="yearly" placeholder="yearly" autocomplete="off">
                                    <div class="input-group-append" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div> -->
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <hr>

        <div class="row mb-3" id="rsm_tsm_filter">
            <div class="col-lg-6">
                <div class="row">



                    <div class="col-lg-2">
                        <div class="form-group mb-2">
                            <a href="{{route('reportsObst')}}" class="btn btn-primary" name="obst" id="obst">OBST</a>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group mb-2">
                            <a href="{{route('reportsDst')}}" class="btn btn-primary" name="dst" id="dst">DST</a>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group mb-2">
                            <a href="{{route('reportsBoa')}}" class="btn btn-primary" name="boa" id="boa">BOA</a>
                        </div>
                    </div>
                </div>


            </div>
            <div class="col-lg-6">

                <div class="col-lg-4">

                    <div class="table-responsive">
                        <form method="get" action="{{ route('OBST.export') }}">
                            <input type="hidden" id="date1" name="date1" value="">
                            <input type="hidden" id="date2" name="date2" value="">
                            <button type="submit" class="btn btn-warning" name="submit">Horizontal Data <i class="fa fa-download"></i></button>
                        </form>
                        <!-- <a class="btn btn-warning" href="{{ route('OBST.export') }}">Horizontal Data</a> -->
                    </div>
                </div>

            </div>
        </div>

        <hr>
        <!-- Filtering -->


        <div class="table-responsive">
            {!! $html->table(['class' => 'table table-hover']) !!}
        </div>
    </div>
</div>
@stop

@section('css')
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
    $(document).ready(function() {
        $('.select2').select2();

        $('#min, #max ,#monthly,#yearly ,#tsm,#rsm,#employee,#role').change(function() {
            window.LaravelDataTables["dataTableBuilder"].draw();
        });

        $("#min").on("change", function() {
            var d1 = $(this).val();
            $('#date1').val(d1);
        });
        $("#max").on("change", function() {
            var d2 = $(this).val();
            $('#date2').val(d2);
        });

        $('#min').datepicker({
            format: 'yyyy-mm-dd',
            todayHighlight: 'TRUE',
            autoclose: true,
            changeMonth: true,
            changeYear: true,
            onSelect: function() {
                window.LaravelDataTables["dataTableBuilder"].draw();
            },
        });

        $("#max").datepicker({
            format: 'yyyy-mm-dd',
            todayHighlight: 'TRUE',
            autoclose: true,
            changeMonth: true,
            changeYear: true,
            onSelect: function() {
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
            onSelect: function() {
                window.LaravelDataTables["dataTableBuilder"].draw();
            },
        })

        $('#yearly').datepicker({
            format: "yyyy",
            startView: "years",
            minViewMode: "years",
            autoclose: true,
            onSelect: function() {
                window.LaravelDataTables["dataTableBuilder"].draw();
            },
        })
    });

    $('#example').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ]
    });
</script>
@stop