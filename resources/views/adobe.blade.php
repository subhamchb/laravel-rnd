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
                                    <select name="template_id" id="template_id" class="form-control"
                                        onchange="getTemplateFields(this.value)">
                                        <option value="">Select template</option>
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

                            <div class="col">
                                <div class="form-group">
                                    <label for="name">Agreement Name</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Give a name to identify it later">
                                </div>
                            </div>
                        </div>

                        <div id="template-fields" class="mt-4">
                            <p>Prefill form fields will apprear here when you select a template.</p>
                        </div>

                        <button type="submit" class="btn btn-dark">Create agreement</button>
                    </form>
                </div>
            </div>

            @include('partials.agreements', ['agreements' => $agreements])
        </div>
    </div>
@endsection

@push('js')
    <script>
        const csrfToken = document.head.querySelector("[name~=csrf-token][content]").content;

        function getTemplateFields(id) {
            fetch('{{ route('adobe.getTemplateFields') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        "X-CSRF-Token": csrfToken
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then((response) => {
                    return response.text();
                })
                .then(html => {
                    document.getElementById('template-fields').innerHTML = '';
                    document.getElementById('template-fields').innerHTML = html;
                })
                .catch(function(error) {
                    console.error('Error:', error);
                });
        }
    </script>
@endpush
