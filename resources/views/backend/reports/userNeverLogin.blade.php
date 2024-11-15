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
        <h3 class="card-title">User Never LoggedIn</h3>
    </div>

    <div class="card-body">
        <!-- Filtering -->
        <!-- <div class="row">
            <div class="col">
                <div id="date_filter">
                    <div class="row">
                        <div class="col">
                            <div class="form-group mb-2">
                                <div class="input-group">
                                    <input type="text" name="dateFrom" class="form-control" id="min" placeholder="From Date" autocomplete="off" required>
                                    <div class="input-group-append" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group mx-sm-3 mb-2">
                                <div class="input-group">
                                    <input type="text" name="dateTo" class="form-control" id="max" placeholder="To Date" autocomplete="off" required>
                                    <div class="input-group-append" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="form-group mx-sm-3 mb-2">
                                <div class="input-group">
                                    <select name="roles" id="roles" class="form-control" required>
                                        <option value="3">OBST</option>
                                        <option value="8">DST</option>
                                        <option value="7">BOA</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group mx-sm-3 mb-2">
                                <input type="submit" name="filter" id="filter" class="btn btn-primary">
                            </div>
                        </div>
                        
                        <input type="hidden" name="role" id="role" value="3">
                    </div>
                </div>
            </div>
        </div> -->
        <!-- <hr> -->


        <div class="row mb-3" id="rsm_tsm_filter">
            
        </div>
        <!-- Filtering -->


        <div class="table-responsive table-result">
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

        // $('#min, #max ,#monthly,#yearly ,#tsm,#rsm,#employee,#role').change(function() {
        //     window.LaravelDataTables["dataTableBuilder"].draw();
        // });
        $('#filter').click(function() {
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

        $('#roles').change(function(){

            var put_role = $(this).val();
            $('#role').val(put_role);
        });
    });
</script>
@stop