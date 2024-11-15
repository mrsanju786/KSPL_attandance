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
             <br>
             <br>
             <div>
             
             <h3 class="card-title" style="padding-left: 20px;color:red;"> * If you are selecting a single date you need to put start and end date as same.
             
             </div>
    </div>

    <div class="card-body">
        <!-- Filtering -->
        <div class="row">
            <div class="col">
                <div id="date_filter">
                    <!-- <form method="get" action="{{ route('reportsObst') }}"> -->
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
                                        <option value="1">All</option>
                                        {{-- <option value="3">OBST</option>
                                        <option value="5">Level1(TSM)</option>
                                        <option value="6">Level2(RSM)</option>
                                        <option value="8">DST</option>
                                        <option value="7">BOA</option> --}}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group mx-sm-3 mb-2">
                                <input type="submit" name="filter" id="filter" class="btn btn-primary">
                            </div>
                        </div>
                        
                        <input type="hidden" name="role" id="role" value="1">
                                
                        <!-- <div class="col">
                            <div class="form-group mx-sm-3 mb-2">
                                <label for="monthly">Select Month</label>
                                <div class="input-group">
                                    <input type="text" name="monthly" class="form-control" id="monthly" placeholder="Monthly" autocomplete="off">
                                    <div class="input-group-append" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <!-- <div class="col">
                            <div class="form-group mx-sm-3 mb-2">
                                <label for="yearly">Select Year</label>
                                <div class="input-group">
                                    <input type="text" name="yearly" class="form-control" id="yearly" placeholder="yearly" autocomplete="off">
                                    <div class="input-group-append" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                    </div>
                    <!-- </form> -->
                </div>
            </div>
        </div>
        <span style="color:red;" id="textWarning"></span>
        <hr>


        <div class="row mb-3" id="rsm_tsm_filter">
            <!-- <div class="col-lg-6">
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


            </div> -->
            <!-- <div class="col-lg-6">

                <div class="col-lg-4">

                    <div class="table-responsive">
                        <form method="get" action="{{ route('export') }}">
                            <input type="hidden" id="date1" name="date1" value="">
                            <input type="hidden" id="date2" name="date2" value="">
                            <button type="submit" class="btn btn-warning" name="submit">Horizontal Data <i class="fa fa-download"></i></button>
                        </form>
                    </div>
                </div>

            </div> -->
        </div>
        <!-- <hr> -->
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
.status {
        padding: 2px;
        border-radius: 3px;
        font-weight: bold;
        text-align: center;
    }

    .present {
        background-color: #18B405;
        color: #fff;
    }

    .absent {
        background-color: #CF2A16;
        color: #fff;
    }
    .out-door{
        background-color: #D5AE0F ;
        color: #fff;
    }
    .holiday{
        background-color: #581845 ;
        color: #fff;
    }
    .early-leave{
        background-color: #F38128 ;
        color: #fff; 
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
    $(document).ready(function() {
        $('.select2').select2();

        // $('#min, #max ,#monthly,#yearly ,#tsm,#rsm,#employee,#role').change(function() {
        //     window.LaravelDataTables["dataTableBuilder"].draw();
        // });
        $('#filter').click(function() {
            var min     = $('#min').val();
            var max     = $('#max').val();
            if((min.length == 0 || max.length == 0)){
                $('#min').addClass('is-invalid');
                $('#max').addClass('is-invalid');
                $('#textWarning').html('*Select date range.');
                return false;
            }else{
                $('#min').removeClass('is-invalid');
                $('#max').removeClass('is-invalid');
                $('#textWarning').remove();
                window.LaravelDataTables["dataTableBuilder"].draw();
                $('.table-result').show();
            }
            
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