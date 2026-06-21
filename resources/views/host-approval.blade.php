<x-layouts.app title="Host Approval">
    <section class="grid two">
        <div class="panel focus">
            <p class="eyebrow">Host decision</p>
            <h1>{{ $log->visitor->full_name }}</h1>
            <p class="muted">{{ $log->visitor->company_name ?: 'No company supplied' }} · {{ $log->visitor->email_address }}</p>
            <dl class="details">
                <div><dt>Status</dt><dd>{{ str_replace('_', ' ', $log->status) }}</dd></div>
                <div><dt>Host</dt><dd>{{ $log->host_employee_id }}</dd></div>
                <div><dt>Submitted</dt><dd>{{ $log->checkin_submit_time?->format('Y-m-d H:i') ?? '-' }}</dd></div>
            </dl>
        </div>

        <form class="panel" method="post" action="{{ route('host.approval.apply', $log) }}">
            @csrf
            <h2>Approve or deny</h2>
            <label>
                Assigned location ID
                <input list="locations" name="assigned_location_id" value="{{ old('assigned_location_id', $log->assigned_location_id) }}" required>
            </label>
            <datalist id="locations">
                @foreach ($locations as $location)
                    <option value="{{ $location->location_id }}">{{ $location->display_name }}</option>
                @endforeach
            </datalist>
            <div class="split-actions">
                <button name="host_action" value="APPROVE" type="submit">Approve and print badge</button>
                <button class="danger" name="host_action" value="DENY" type="submit">Deny entry</button>
            </div>
            @if ($log->status === 'APPROVED')
                <a class="button secondary full" href="{{ route('wayfinding.show', $log) }}">Open wayfinding</a>
            @endif
        </form>
    </section>
</x-layouts.app>
