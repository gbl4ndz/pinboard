<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Live Board</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-stone-50 text-stone-800 min-h-screen">

    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="flex items-center gap-3 mb-10">
            <span class="text-2xl">🌾</span>
            <h1 class="text-3xl font-semibold text-stone-800 tracking-tight">{{ config('app.name') }}</h1>
            <span class="ml-auto flex items-center gap-2 text-sm text-green-600 font-medium">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse inline-block"></span>
                Live
            </span>
        </div>

        <p class="text-stone-400 text-center py-20 text-lg">Board coming in Module 6.</p>
    </div>

</body>
</html>
