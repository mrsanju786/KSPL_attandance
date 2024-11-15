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

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
        {{ Form::hidden('id', $data->id, array('id' => 'id')) }}

        <div class="card-body">

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Import Data Type</strong>
                </div>
                <div class="col-sm-10 col-content p-0">
                    <select class="form-control" name="import_type" required>
                        <option value="">Select</option>
                        <option value="1">RSM</option>
                        <option value="2">TSM</option>
                        <option value="3">OBST</option>
                        <option value="4">BOA</option>
                        <option value="5">DST</option>
                        <!-- <option value="6">OBST TSM</option> -->
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Import Data</strong>
                </div>
                <div class="col-sm-10 col-content">
                    <input type="file" class="custom-file-input" name="import" id="import" required>
                    <label class="custom-file-label" for="customFile">Choose file</label>
                    <span class="image-upload-label">Please upload the csv File</span>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Download Template</strong>
                </div>
                <div class="col-sm-2 col-content">
                    <a href="{{ asset('img/rsm_template.csv') }}" download="rsm_template.csv"><button type="button" class="btn btn-success"> Download Template CSV (RSM)</button></a>
                </div>
                <div class="col-sm-2 col-content">
                    <a href="{{ asset('img/tsm_template.csv') }}" download="tsm_template.csv"><button type="button" class="btn btn-success"> Download Template CSV (TSM)</button></a>
                </div>
                <div class="col-sm-2 col-content">
                    <a href="{{ asset('img/obst_template.csv') }}" download="obst_template.csv"><button type="button" class="btn btn-success"> Download Template CSV (OBST)</button></a>
                </div>
                <div class="col-sm-2 col-content">
                    <a href="{{ asset('img/dht_template.csv') }}" download="dht_template.csv"><button type="button" class="btn btn-success"> Download Template CSV (DST)</button></a>
                </div>
                <div class="col-sm-2 col-content">
                    <a href="{{ asset('img/boa_template.csv') }}" download="boa_template.csv"><button type="button" class="btn btn-success"> Download Template CSV (BOA)</button></a>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Instructions</strong>
                </div>
                <div class="col-sm-10 col-content">
                    <button type="button" class="btn btn-info collapsed" data-toggle="collapse" data-target="#instructions" aria-expanded="false">Show Instructions </button>
                    <div id="instructions" class="collapse col-content" aria-expanded="false">
                        <img src="{{ asset('img/import_csv.png') }}" class="img-fluid">
                    </div>
                </div>
            </div>

        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">{{ $data->button_text }}</button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>

    <!-- /.card -->
    </div>
    <!-- /.row -->
    <!-- /.content -->
@stop

@section('css')

@stop

@section('js')
    <script>var typePage = "{{ $data->page_type }}";
     $('#import').on('change',function(){
        //get the file name
        var fileName = $(this).val();
        //replace the "Choose a file" label
        $(this).next('.custom-file-label').html(fileName);
    })
    </script>

    <script src="{{ asset('js/backend/histories/form.js'). '?v=' . rand(99999,999999) }}"></script>
@stop
