@if (\Session::has('errors'))
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach (\Session::get('errors') as $error)
                <li>{{ $error->message }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (\Session::has('error'))
    <div class="alert alert-danger">
        <ul class="mb-0">
            <li>{{ \Session::get('error') }}</li>
        </ul>
    </div>
@endif

@if (\Session::has('success'))
    <div class="alert alert-success">
        <ul class="mb-0">
            <li>{{ \Session::get('success')['text'] }}</li>
            @if (\Session::get('success')['data'])
                <li><code>{{ \Session::get('success')['data'] }}</code></li>
            @endif
        </ul>
    </div>
@endif
