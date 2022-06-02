<div class="row mb-4">
    <div class="col">
        <div class="form-group">
            <label for="type">Select type</label>
            <select name="type" id="type" class="form-control">
                <option value="">Select Type</option>
                @foreach ($requirements as $item)
                    <option value="{{ $item->type }}">{{ $item->title }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

@foreach ($requirements as $rqr)
    <div class="row">
        <h4>{{ $rqr->title }}</h4>
        @foreach ($rqr->fields as $field)
            <div class="col-6">
                <div class="form-group">
                    <label for="{{ $field->group[0]->key }}">{{ $field->name }} @if ($field->group[0]->required)
                            *
                        @endif
                    </label>

                    @if ($field->group[0]->type === 'select')
                        <select name="details[{{ $field->group[0]->key }}]" id="{{ $field->group[0]->key }}"
                            class="form-control mb-4" @if ($field->group[0]->required) required @endif>
                            @foreach ($field->group[0]->valuesAllowed as $type)
                                <option value="{{ $type->key }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="details[{{ $field->group[0]->key }}]"
                            @if ($field->group[0]->example) placeholder="eg. {{ $field->group[0]->example }}" @endif
                            id="{{ $field->group[0]->key }}" class="form-control mb-4"
                            @if ($field->group[0]->required) required @endif>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endforeach
