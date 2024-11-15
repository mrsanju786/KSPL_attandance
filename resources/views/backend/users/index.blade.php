{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Users  | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Users ({{ $userCount }})</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List</h3>
            <!-- Filtering -->
            <!-- <div id="date_filter" class="form-inline">
                <div class="form-group mx-sm-3 mb-2">
                    <label for="to"></label>
                    <div class="input-group">
                        <form method="get" action="{{route('users')}}">
                            <input type="hidden" name="status" id="status" value="1">
                            <button type="submit" name="submit" class="btn btn-primary btn-xs" style="font-size: large;">Active</button>
                        </form>
                    </div>
                    &nbsp;
                    <form method="get" action="{{route('users')}}">
                            <input type="hidden" name="status" id="status" value="0">
                            <button type="submit" name="submit" class="btn btn-primary btn-xs" style="font-size: large;">Deactive</button>
                        </form>
                </div>
            </div> -->
            <!-- Filtering -->
        </div>

        <div class="card-body">
            <div class="table-responsive">
                {!! $html->table(['class' => 'table table-hover']) !!}
            </div>
        </div>
    </div>


    <!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
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
    <script src="{{ asset('js/main_index.js') }}"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>

    <script>
       
    $('#myModal').on('click', function () {
        console.log('test');
        alert('test1');
    // $('#myInput').trigger('focus')
    })

</script>    
@stop
