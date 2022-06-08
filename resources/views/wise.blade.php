@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    Add Bank Details
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="currency">Currency *</label>
                                <select name="currency" id="currency" class="form-control"
                                    onchange="getFormFields(this.value)">
                                    <option value="">Select your currency</option>
                                    <option value="USD">USD</option>
                                    <option value="GBP">GBP</option>
                                    <option value="INR">INR</option>
                                    <option value="EUR">EUR</option>
                                    <option value="MXN">MXN</option>
                                    <option value="PHP">PHP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="bank-details" class="mt-4"></div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    Recipient List
                </div>
                <div class="card-body">

                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Currency</th>
                            <th>Bank Details</th>
                            <th>Action</th>
                        </tr>

                        @foreach ($list as $bank)
                            <tr>
                                <td>{{ $bank->id }}</td>
                                <td>{{ $bank->accountHolderName }}</td>
                                <td>{{ $bank->currency }}</td>
                                <td class="code">
                                    <code
                                        style="">{{ preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', json_encode($bank->details)) }}</code>
                                </td>
                                <td>
                                    <a href="{{ route('wise.deleteMember', ['accountID' => $bank->id]) }}"
                                        class="btn btn-danger">Delete</a>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        const csrfToken = document.head.querySelector("[name~=csrf-token][content]").content;

        function getFormFields(currency) {
            if (currency !== '') {
                fetch('{{ route('wise.getFormFields') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            "X-CSRF-Token": csrfToken
                        },
                        body: JSON.stringify({
                            currency: currency
                        })
                    })
                    .then((response) => {
                        return response.text();
                    })
                    .then(html => {
                        document.getElementById('bank-details').innerHTML = '';
                        document.getElementById('bank-details').innerHTML = html;
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                    });
            }
        }

        function refreshed(form) {
            var formData = new FormData(document.getElementById(form));
            fetch('{{ route('wise.getChildFormFields') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        "X-CSRF-Token": csrfToken
                    },
                    body: formData
                })
                .then((response) => {
                    return response.text();
                })
                .then(html => {
                    document.getElementById('bank-details').innerHTML = '';
                    document.getElementById('bank-details').innerHTML = html;
                })
                .catch(function(error) {
                    console.error('Error:', error);
                });
        }
    </script>
@endpush
