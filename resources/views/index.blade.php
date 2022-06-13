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
                            <div class="card">
                                <div class="card-body">
                                    <a href="{{ route('wise.index') }}" type="button"
                                        class="btn btn-dark w-100">Transferwise
                                        API</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="card">
                                <div class="card-body">
                                    <a href="{{ route('adobe.index') }}" type="button" class="btn btn-dark w-100">Adobe
                                        API</a>
                                    <ul class="list-group mt-2">
                                        <li class="list-group-item"><a target="_blank"
                                                href="https://documenter.getpostman.com/view/14752/TzkyLzWB#a3a54469-3fd3-4bd1-92ea-0ac0d46240ab">Add
                                                merge data</a></li>
                                    </ul>
                                </div>
                            </div>
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
