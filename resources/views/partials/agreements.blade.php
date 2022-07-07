<div class="card mt-4">
    <div class="card-header">
        Agreements
    </div>

    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Signer Email</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @if (isset($agreements->userAgreementList) && count($agreements->userAgreementList) > 0)
                    @foreach ($agreements->userAgreementList as $agreement)
                        <tr>
                            <td>
                                @if (isset($agreement->name) && $agreement->name !== '')
                                    {{ $agreement->name }} <br>
                                @else
                                    Not available <br>
                                @endif

                                <code>{{ $agreement->id }}</code>
                            </td>
                            <td>
                                @if (isset($agreement->displayParticipantSetInfos))
                                    {{ $agreement->displayParticipantSetInfos[0]->displayUserSetMemberInfos[0]->email }}
                                @else
                                    Not available
                                @endif
                            </td>
                            <td>{{ $agreement->status }}</td>
                            <td>{{ $agreement->displayDate }}</td>
                            <td>
                                <a href="{{ route('adobe.viewAgreement', ['id' => $agreement->id, 'status' => $agreement->status, 'email' => 'subham.c@voyantcs.com']) }}"
                                    target="_blank"
                                    class="btn btn-dark @if ($agreement->status == 'CANCELLED' || $agreement->status == 'DRAFT') disabled @endif">View
                                    agreement</a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5">No Agreements Found</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
