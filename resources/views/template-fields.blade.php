<div class="row">
    @foreach ($fields as $field)
        <div class="col-4 mb-4">
            <div class="form-group">
                <label for="{{ $field->name }}">{{ $field->name }}</label>
                <input type="text" class="form-control" id="{{ $field->name }}"
                    name="mergedata['{{ $field->name }}']" value="{{ $field->defaultValue }}"
                    placeholder="{{ $field->name }}@if ($field->validationData != 'NONE') , eg. {{ $field->validationData }} @endif"
                    @if ($field->readOnly) readonly @endif @if ($field->required) required @endif>
            </div>
        </div>
    @endforeach
</div>
