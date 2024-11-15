{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Roles  | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Roles</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                {!! $html->table(['class' => 'table table-hover']) !!}
            </div>
        </div>
    </div>
@stop

<!-- Include this modal in your Blade file -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Role</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form for editing attendance -->
                <form id="editForm">
                    @csrf
                    <div class="form-group">
                        <label for="checkOut">Role Name:</label>
                        <input type="text" class="form-control" id="roleName" name="role_name">
                    </div>
                    <!-- Add other input fields as needed -->

                    <!-- Add attendance ID as a hidden input -->
                    <input type="hidden" id="roleId" name="role_id">

                    <button type="button" class="btn btn-primary" id="updateRoleBtn">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

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
    <!-- Include SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script src="{{ asset('js/main_index.js'). '?v=' . rand(99999,999999) }}"></script>

    <script>
    $(document).ready(function () {
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var dataId = button.data('id'); // Extract data-id value from data-* attributes
            var displayName = button.data('display-name');

            try {
 
            var modal = $(this);

            modal.find('#roleId').val(dataId);
            modal.find('#roleName').val(displayName);

        } catch (error) {
            console.error('Error parsing JSON:', error);
        }
        });

    
   });
   $('#updateRoleBtn').on('click', function () {

    var roleId = $('#roleId').val();
    var roleName = $('#roleName').val();

    $.ajax({
        url: '{{ url("/update-role") }}/' + roleId,
        type: 'POST',
        data: {
            _token: $('input[name="_token"]').val(),
            role_name: roleName,
        },
        success: function (response) {
            if(response.success == true){
                // Handle success response (e.g., close modal, refresh table, etc.)
                $('#editModal').modal('hide');
                // Display SweetAlert success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Role updated successfully.',
                    }).then(() => {
                        // Redirect to a specific URL after successful update
                        window.location.href = '{{ route("roles") }}';
                    });
                }else{

                    Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'The entered role name already exists.',
                        });
                }
            },
        error: function (xhr) {
            // Handle error response
            console.log(xhr.responseText);
        }
    });
    });


    </script>
@stop
