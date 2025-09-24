<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="min-h-screen flex">
    <aside class="w-64 bg-gray-800 text-gray-100 p-4">
        <h1 class="text-2xl font-bold mb-6">Admin Panel</h1>
        <nav class="flex flex-col gap-2">
            <a href="{{ route('admin.users.index') }}" class="hover:text-indigo-400">Users</a>
            <a href="{{ route('admin.roles.index') }}" class="hover:text-indigo-400">Roles</a>
            <a href="{{ route('admin.permissions.index') }}" class="hover:text-indigo-400">Permissions</a>
        </nav>
    </aside>
    <main class="flex-1 p-6">
        @yield('content')
    </main>
</div>
</body>
</html>
