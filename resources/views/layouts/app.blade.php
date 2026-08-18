<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Taskflow' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="shell">
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="flash flash-error">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
