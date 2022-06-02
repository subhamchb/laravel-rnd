@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Tests
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-2">
                            <a href="{{ route('wise.index') }}" type="button" class="btn btn-dark w-100">Transferwise
                                API</a>
                        </div>
                        <div class="col-2">
                            <a href="{{ route('adobe.index') }}" type="button" class="btn btn-dark w-100">Adobe API</a>
                        </div>
                        <div class="col-2"></div>
                        <div class="col-2"></div>
                        <div class="col-2"></div>
                        <div class="col-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
