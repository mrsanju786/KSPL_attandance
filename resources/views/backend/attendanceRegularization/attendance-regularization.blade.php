{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Attendance Regularization | ' . Config::get('adminlte.title'))

@section('content_header')
<h1>Attendance Regularization</h1>
@stop

@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{--Show message if any--}}
@include('layouts.flash-message')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Attendance Regularization</h3>
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
                        
                        <!-- <div class="col">
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
                        </div> -->

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

                    
<!-- 
                        <div class="col mt-4">
                            <div class="form-group mx-sm-3 mb-2">
                                <input type="submit" name="filter" id="attendancefilter" class="btn btn-primary" value="Filter">
                                <input type="submit" name="attendance_save" id="attendance_save" class="btn btn-primary" value="Update" style="display:none;">
                            </div>
                        </div> -->

                    </div>
                    <!-- </form> -->
                </div>
            </div>
            <span style="color:red;" id="textWarning"></span>
            <hr>
            <div class="table-responsive table-result">
                {!! $html->table(['class' => 'table table-hover']) !!}
            </div>
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

<!-- Include this modal in your Blade file -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Attendance</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form for editing attendance -->
                <form id="editForm">
                    @csrf
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select class="form-control" id="attendanceStatusDropdown" name="status">
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="checkIn">Check-In Time:</label>
                        <input type="time" class="form-control" id="checkIn" name="check_in">
                    </div>
                    <div class="form-group">
                        <label for="checkOut">Check-Out Time:</label>
                        <input type="time" class="form-control" id="checkOut" name="check_out">
                    </div>
                    <!-- Add other input fields as needed -->

                    <!-- Add attendance ID as a hidden input -->
                    <input type="hidden" id="attendanceId" name="attendance_id">
                    <input type="hidden" id="attendanceDate" name="date">
                    <input type="hidden" id="workerId" name="worker_id">
                    <input type="hidden" id="workerRoleId" name="worker_role_id">
                    <button type="button" class="btn btn-primary" id="updateAttendanceBtn">Update <span id="spinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span></button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="reasonModal" tabindex="-1" role="dialog" aria-labelledby="reasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reasonModalLabel">Reason</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="reasonText"></p>
            </div>
        </div>
    </div>
</div>

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
    <!-- Include SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

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


        $(document).ready(function() {
            // Attach click event to the view reason button
            $(document).on('click', '.view-reason', function() {
                var reason = $(this).data('reason'); // Get the reason from the data attribute
                $('#reasonText').text(reason); // Set the reason text in the modal
                $('#reasonModal').modal('show'); // Show the modal
            });
        });

        $(document).ready(function() {

            $(document).on('click', '.confirm-status-update', function(e) {
                e.preventDefault(); 
                var url = $(this).attr('href'); // Get the URL from the link
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, proceed!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url; // Redirect to the URL if confirmed
                    }
                });
            });
        });


        function statusRequest(id, status) {
            // Log the parameters for debugging
            console.log('Request ID:', id, 'Status:', status);
            // Send an AJAX request to the server to update the status
            $.ajax({
                url: '{{ url("/update-status") }}',
                method: 'POST',
                data: {
                    id: id,
                    status: status,
                    _token: '{{ csrf_token() }}' // Include the CSRF token for security
                },
                success: function(response) {
                    // Handle the success response
                    console.log('Status updated:', response);
                    // Optionally, you can refresh the table or update the UI
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Attendance updated successfully.',
                    }).then(() => {
                        // Redirect to a specific URL after successful update
                        window.location.href = '{{ route("attendance-regularization") }}';
                    });

                },
                error: function(xhr, status, error) {
                    // Handle the error response
                    console.error('Error updating status:', xhr, status, error);
                }
            });
        }



    // Handle form submission (update the attendance)
    $('#updateAttendanceBtn').on('click', function () {

        var attendanceId = $('#attendanceId').val();
        var attendanceDate = $('#attendanceDate').val();
        var workerId = $('#workerId').val();
        var workerRoleId = $('#workerRoleId').val();
        var status = $('#attendanceStatusDropdown').val();
        var checkIn = $('#checkIn').val();
        var checkOut = $('#checkOut').val();

        // Show spinner and disable the button
        $('#spinner').show();
        $('#updateAttendanceBtn').prop('disabled', true);
        //console.log(status);
        // Perform AJAX request to update the attendance record
        $.ajax({
            url: '{{ url("/update-attendance") }}/' + attendanceId,
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                status: status,
                date:attendanceDate,
                worker_id: workerId,
                worker_role_id:workerRoleId,
                check_in: checkIn,
                check_out: checkOut,
            },
            success: function (response) {
                // Handle success response (e.g., close modal, refresh table, etc.)
                $('#editModal').modal('hide');
                    // Show success alert message
                    // Display SweetAlert success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Attendance Status updated successfully.',
                    }).then(() => {
                        // Redirect to a specific URL after successful update
                        window.location.href = '{{ route("attendance-management") }}';
                    });
                },
            error: function (xhr) {
                // Handle error response
                console.log(xhr.responseText);
            },
            complete: function () {
                // Hide spinner and enable the button
                $('#spinner').hide();
                $('#updateAttendanceBtn').prop('disabled', false);
           }
        });
    });


    });

 




</script>
@stop