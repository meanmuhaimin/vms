<x-layouts.app title="Check-In Status">
    <section class="panel focus">
        <p class="eyebrow">Visitor status</p>
        <h1>{{ $log->visitor->full_name }}</h1>
        <span class="status {{ strtolower($log->status) }}">{{ str_replace('_', ' ', $log->status) }}</span>
        <dl class="details">
            <div><dt>Log ID</dt><dd>{{ $log->log_id }}</dd></div>
            <div><dt>Host</dt><dd>{{ $log->host_employee_id }}</dd></div>
            <div><dt>Destination</dt><dd>{{ $location?->display_name ?? $log->assigned_location_id }}</dd></div>
            <div><dt>Submitted</dt><dd>{{ $log->checkin_submit_time?->format('Y-m-d H:i') ?? 'Pending' }}</dd></div>
        </dl>
        <div class="actions">
            <a class="button secondary" href="{{ route('host.approval', $log) }}">Open host approval</a>
            @if ($log->status === 'APPROVED')
                <a class="button" href="{{ route('wayfinding.show', $log) }}">View wayfinding</a>
            @endif
        </div>
    </section>
</x-layouts.app>
