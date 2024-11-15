
@extends('adminlte::page')

@section('title', 'Od | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Od Management</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"> Staff od List</h3>
            <br>
            

            <form class="form-inline" action="{{ url('od')}}" method="GET" style="float: right;">
                <div class="form-group mb-2">
                    <label for="staticEmail2" class="sr-only">Role</label>
                    <!-- <input type="text" readonly class="form-control-plaintext" id="staticEmail2" value="email@example.com"> -->
                </div>
                <div class="form-group mx-sm-3 mb-2">
                    <label for="inputPassword2" class="sr-only">Password</label>
                </div>
                <button type="submit" class="btn btn-primary mb-2">Submit</button>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                {!! $html->table(['class' => 'table table-hover']) !!}
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables-plugins/buttons/css/buttons.bootstrap4.css') }}" rel="stylesheet">
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
    <script src="{{ asset('js/main_index.js'). '?v=' . rand(99999,999999) }}"></script>

    
@stop
