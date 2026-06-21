<x-layouts.app title="Visitor Check-In">
    <section class="hero">
        <div>
            <p class="eyebrow">Supervised mobile check-in</p>
            <h1>Register before stepping to the counter.</h1>
            <p class="hero-copy">Submit your visit details, request phone verification, and let reception coordinate host approval and badge printing.</p>
        </div>
        <div class="hero-card">
            <span class="metric">30s</span>
            <span>Target front-desk handling time after visitor self-registration.</span>
        </div>
    </section>

    <section class="grid two">
        <form class="panel" method="post" action="{{ route('otp.request') }}">
            @csrf
            <h2>1. Request OTP</h2>
            <p class="muted">Use international format, for example <code>+60123456789</code>.</p>
            <label>
                Phone number
                <input name="phone_number" value="{{ old('phone_number') }}" placeholder="+60123456789" required>
            </label>
            <button type="submit">Send OTP</button>
        </form>

        <form class="panel" method="post" action="{{ route('check-in.store') }}">
            @csrf
            <h2>2. Submit Check-In</h2>
            <div class="fields">
                <label>
                    Full name
                    <input name="full_name" value="{{ old('full_name') }}" required>
                </label>
                <label>
                    Phone number
                    <input name="phone_number" value="{{ old('phone_number') }}" placeholder="+60123456789" required>
                </label>
                <label>
                    Email
                    <input type="email" name="email_address" value="{{ old('email_address') }}" required>
                </label>
                <label>
                    Company
                    <input name="company_name" value="{{ old('company_name') }}">
                </label>
                <label>
                    Document type
                    <select name="id_doc_type" required>
                        @foreach (['MYKAD', 'PASSPORT', 'OTHER'] as $type)
                            <option value="{{ $type }}" @selected(old('id_doc_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    ID number
                    <input name="id_number" value="{{ old('id_number') }}" required>
                </label>
                <label>
                    Host employee ID
                    <input name="host_employee_id" value="{{ old('host_employee_id') }}" placeholder="EMP-001" required>
                </label>
                <label>
                    Destination location ID
                    <input list="locations" name="assigned_location_id" value="{{ old('assigned_location_id') }}" placeholder="CONF_ROOM_12B" required>
                </label>
            </div>

            <datalist id="locations">
                @foreach ($locations as $location)
                    <option value="{{ $location->location_id }}">{{ $location->display_name }}</option>
                @endforeach
            </datalist>

            <button type="submit">Submit check-in</button>
        </form>
    </section>
</x-layouts.app>
