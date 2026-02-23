<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-100 text-slate-800" style="font-family: Manrope, system-ui, sans-serif;">
<div class="min-h-screen flex">
    <aside class="w-64 border-r border-slate-200 bg-white p-5">
        <h1 class="mb-6 text-xl font-bold tracking-tight" style="font-family: 'Space Grotesk', Manrope, sans-serif;">Admin Panel</h1>
        <nav class="flex flex-col gap-2 text-sm">
            <a href="{{ route('admin.users.index') }}" class="rounded-lg px-3 py-2 hover:bg-cyan-50 hover:text-cyan-700">Users</a>
            <a href="{{ route('admin.roles.index') }}" class="rounded-lg px-3 py-2 hover:bg-cyan-50 hover:text-cyan-700">Roles</a>
            <a href="{{ route('admin.permissions.index') }}" class="rounded-lg px-3 py-2 hover:bg-cyan-50 hover:text-cyan-700">Permissions</a>
        </nav>
    </aside>
    <main class="flex-1 p-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
