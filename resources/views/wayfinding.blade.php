<x-layouts.app title="Wayfinding">
    <section class="panel focus route-card">
        <p class="eyebrow">Approved route</p>
        <h1>{{ $location?->display_name ?? $log->assigned_location_id }}</h1>
        <p class="muted">{{ $location?->building_sector ?? 'Building' }} · {{ $location?->floor_label ?? 'Floor pending' }}</p>

        @if ($location?->map_asset_path)
            <div class="map-box">Map asset: <code>{{ $location->map_asset_path }}</code></div>
        @endif

        <ol class="steps">
            @forelse (($location?->route_steps ?? []) as $step)
                <li>{{ $step }}</li>
            @empty
                <li>Proceed to reception for route guidance.</li>
                <li>Ask the guard to confirm destination {{ $log->assigned_location_id }}.</li>
            @endforelse
        </ol>
    </section>
</x-layouts.app>
