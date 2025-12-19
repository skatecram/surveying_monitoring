<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Überwachungs Programm')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    <style>
        .threshold-warning { background-color: #FFFF00; }
        .threshold-caution { background-color: #FFA500; }
        .threshold-alarm { background-color: #FF0000; color: white; }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-900 text-white p-4">
        <div class="container mx-auto flex flex-wrap justify-between items-center gap-2">
            <h1 class="text-xl sm:text-2xl font-bold">Überwachungs Programm</h1>
            <div class="space-x-4">
                <a href="{{ route('projects.index') }}" class="hover:underline">Projekte</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-6 px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
        {{ $slot ?? '' }}
    </div>
    @fluxScripts
</body>
</html>
