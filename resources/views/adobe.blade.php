@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    Create agreement

                    <a class="text-danger float-end" href="{{ (new \App\Services\AdobeService())->getCode() }}">Adobe
                        Credentials</a>
                </div>

                <div class="card-body">
                    <form action="{{ route('adobe.createAgreement') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="template_id">Select template</label>
                                    <select name="template_id" id="template_id" class="form-control">
                                        @foreach ($templates->libraryDocumentList as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="john@doe.com">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col">
                                <div class="form-group">
                                    <label for="name">Agreement Name</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Give a name to identify it later">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark mt-4">Create agreement</button>
                    </form>
                </div>
            </div>

            @include('partials.agreements', ['agreements' => $agreements])
        </div>
    </div>
@endsection
