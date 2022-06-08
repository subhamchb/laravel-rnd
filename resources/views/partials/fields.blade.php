<div class="row">
    <div class="col">
        <ul class="nav nav-pills nav-justified mb-3" id="pills-tab" role="tablist">
            @foreach ($requirements as $rqr)
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($activeTab && $activeTab == $rqr->type) active @endif"
                        id="pills-{{ $rqr->type }}-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-{{ $rqr->type }}" type="button" role="tab"
                        aria-controls="pills-{{ $rqr->type }}" aria-selected="true">{{ $rqr->title }}</button>
                </li>
            @endforeach
        </ul>
        <div class="tab-content" id="pills-tabContent">
            @foreach ($requirements as $rqr)
                <div class="tab-pane fade show @if ($activeTab && $activeTab == $rqr->type) active @endif"
                    id="pills-{{ $rqr->type }}" role="tabpanel" aria-labelledby="pills-{{ $rqr->type }}-tab"
                    tabindex="0">
                    <div class="col">
                        <form action="{{ route('wise.createRecipient') }}" method="POST" class="mb-4"
                            id="form_{{ $rqr->type }}">
                            @csrf
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="accountHolderName">Account Holder Name *</label>
                                        <input type="text" name="accountHolderName" id="accountHolderName"
                                            placeholder="eg. John Doe" class="form-control"
                                            value="{{ $old == null ? '' : $old->accountHolderName }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @foreach ($rqr->fields as $field)
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="{{ $field->group[0]->key }}">{{ $field->name }}
                                                @if ($field->group[0]->required)
                                                    *
                                                @endif
                                            </label>

                                            @if ($field->group[0]->type === 'select')
                                                <select name="details[{{ $field->group[0]->key }}]"
                                                    id="{{ $field->group[0]->key }}" class="form-control mb-4"
                                                    @if ($field->group[0]->required) required @endif
                                                    @if ($field->group[0]->refreshRequirementsOnChange) onchange="refreshed('form_{{ $rqr->type }}')" @endif>
                                                    @foreach ($field->group[0]->valuesAllowed as $type)
                                                        <option value="{{ $type->key }}"
                                                            @if ($old != null && isset($old->details[$field->group[0]->key]) && $old->details[$field->group[0]->key] == $type->key) selected @endif>
                                                            {{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" name="details[{{ $field->group[0]->key }}]"
                                                    @if ($field->group[0]->example) placeholder="eg. {{ $field->group[0]->example }}" @endif
                                                    id="{{ $field->group[0]->key }}" class="form-control mb-4"
                                                    @if ($field->group[0]->required) required @endif
                                                    value="{{ $old != null && isset($old->details[$field->group[0]->key]) ? $old->details[$field->group[0]->key] : '' }}">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="type" value="{{ $rqr->type }}">
                            <input type="hidden" name="currency" value="{{ $currency }}">
                            <button type="submit" class="btn btn-dark">Save Details</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
