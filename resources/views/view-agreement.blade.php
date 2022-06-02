@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    View Agreement for: <strong>{{ $agreement->signingUrlSetInfos[0]->signingUrls[0]->email }}</strong>
                </div>

                <div class="card-body">
                    <iframe id="signin_frame" src="{{ $agreement->signingUrlSetInfos[0]->signingUrls[0]->esignUrl }}"
                        frameborder="0" width="100%" height="700px"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
