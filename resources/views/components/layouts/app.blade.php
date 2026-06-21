<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'NextGen VMS' }}</title>
    <link rel="stylesheet" href="/vms-ui.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}">NextGen VMS</a>
        <nav class="nav">
            <a href="{{ route('home') }}">Visitor</a>
            <a href="{{ route('reception.index') }}">Reception</a>
        </nav>
    </header>

    <main class="shell">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert">
                <strong>Check the form:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
