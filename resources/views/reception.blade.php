<x-layouts.app title="Reception Dashboard">
    <section class="section-head">
        <div>
            <p class="eyebrow">Counter operations</p>
            <h1>Reception Dashboard</h1>
        </div>
        <a class="button secondary" href="{{ route('home') }}">New visitor</a>
    </section>

    <section class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Visitor</th>
                        <th>Host</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>
                                <strong>{{ $log->visitor->full_name }}</strong><br>
                                <span class="muted">{{ $log->visitor->phone_number }}</span>
                            </td>
                            <td>{{ $log->host_employee_id }}</td>
                            <td>{{ $log->assigned_location_id }}</td>
                            <td><span class="status {{ strtolower($log->status) }}">{{ str_replace('_', ' ', $log->status) }}</span></td>
                            <td>{{ $log->checkin_submit_time?->format('H:i') ?? '-' }}</td>
                            <td class="row-actions">
                                <a href="{{ route('check-in.status', $log) }}">Status</a>
                                <a href="{{ route('host.approval', $log) }}">Host</a>
                                @if ($log->status !== 'CHECKED_OUT')
                                    <form method="post" action="{{ route('reception.release', $log) }}">@csrf<button>Release</button></form>
                                    <form method="post" action="{{ route('reception.checkout', $log) }}">@csrf<button>Checkout</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No visitors yet. Submit a check-in from the visitor page.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
