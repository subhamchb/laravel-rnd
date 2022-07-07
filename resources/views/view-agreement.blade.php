@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <iframe id="signin_frame" src="{{ $agreementUrl }}" frameborder="0" width="100%" height="700px"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
