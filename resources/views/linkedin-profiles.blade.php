<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased">
<div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-blue-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
    <table class="table-auto">
        <thead>
        <tr class="border-b border-gray-500">
            <th class="px-4 py-2">Username</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2">Created At</th>
        </tr>
        </thead>
        <tbody>
        @foreach($profiles as $profile)
            <tr>
                <td class="px-4 py-1">{{ $profile->username }}</td>
                <td class="px-4 py-1">{{ $profile->status }}</td>
                <td class="px-4 py-1">{{ $profile->created_at->toDatetimeString() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
