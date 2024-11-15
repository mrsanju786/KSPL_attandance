@extends('adminlte::page')
<!-- page title -->
@section('title', 'Import Data ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Import Data</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Import</h3>
        </div>

        {{ Form::open(array('url' => route('leavebalance.importData'), 'method' => 'POST','autocomplete' => 'off', 'files' => true,'enctype' => 'multipart/form-data')) }}
        
        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Import Data</strong>
                </div>
                <div class="col-sm-10 col-content">
                    <input type="file" class="custom-file-input" name="import" required>
                    <label class="custom-file-label" for="customFile">Choose file</label>
                    <span class="image-upload-label">Please upload the csv File</span>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Download Template</strong>
                </div>
                <div class="col-sm-2 col-content">
                    <a href="{{ asset('img/LeaveBalance.csv') }}" download=""><button type="button" class="btn btn-success"> Download Template CSV (Leave Balance)</button></a>
                </div>
                
            </div>

        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">Upload</button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>

    <!-- /.card -->
    </div>
    <!-- /.row -->
    <!-- /.content -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();  // Get the selected file name
            $(this).next('.custom-file-label').html(fileName); // Update the label text
        });
    });
</script>
@stop


