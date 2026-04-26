<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Pinboard') }}</title>

        <!-- Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-stone-50 text-stone-800"
          style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">

            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2.5 mb-8">
                <div class="w-9 h-9 rounded-xl bg-green-600 flex items-center justify-center shadow-md shadow-green-900/20">
                    <svg class="w-4.5 h-4.5 text-white w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-stone-900 tracking-tight">Pinboard</span>
            </a>

            <div class="w-full max-w-md">
                <div class="card p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
