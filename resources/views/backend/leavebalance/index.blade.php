{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Leave Balance  | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Leave Balance</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List</h3>

            <form class="form-inline" action="{{url('leavebalance')}}" method="GET" style="float: right;">
            <div id="date_filter" class="form-inline">
                
            <div class="form-group mx-sm-3 mb-2">
                    <label for="to"></label>
                    <div class="input-group">
                        <select class="form-control"  name="year" placeholder="Select Year">
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
                    </div>
                </div>

         
                <div class="form-group mx-sm-3 mb-2">
                    <label for="to"></label>
                    <div class="input-group">
                        <input type="submit"  value="submit" class="btn btn-primary">
                    </div>
                </div>
                <span style="color:red;" id="textWarning"></span>
            </div>
            </form>
        </div>

        <div class="card-body">
            <!-- Filtering -->
            
            <!-- <hr> -->
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
        $(document).ready(function () {
            // $('#min, #max').change(function () {
            //     window.LaravelDataTables["dataTableBuilder"].draw();
            // });

            $('#filter').click(function(){
                var min     = $('#min').val();
                var max     = $('#max').val();
                var regions = $('#regions').val();
                if((min.length == 0 || max.length == 0) && regions.length == 0){
                    $('#min').addClass('is-invalid');
                    $('#max').addClass('is-invalid');
                    $('#textWarning').html('*Select date range or Region.');
                    return false;
                }else{
                    $('#min').removeClass('is-invalid');
                    $('#max').removeClass('is-invalid');
                    $('#textWarning').remove();
                    window.LaravelDataTables["dataTableBuilder"].draw();
                }
                
            });

            $('#min').datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: 'TRUE',
                autoclose: true,
                changeMonth: true,
                changeYear: true,
                // onSelect: function () {
                //     window.LaravelDataTables["dataTableBuilder"].draw();
                // },
            });

            $("#max").datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: 'TRUE',
                autoclose: true,
                changeMonth: true,
                changeYear: true,
                // onSelect: function () {
                //     window.LaravelDataTables["dataTableBuilder"].draw();
                // },
            });

            $('#regions').change(function() {    
                var val=$(this).val();
                $('#region').val(val);
            });
        });
    </script>
@stop
