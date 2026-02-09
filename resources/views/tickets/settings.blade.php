<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __('Ticket Settings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="rounded bg-green-50 px-4 py-2 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach([
                    ['key' => 'status', 'title' => 'Statuses', 'items' => $statuses],
                    ['key' => 'priority', 'title' => 'Priorities', 'items' => $priorities],
                    ['key' => 'category', 'title' => 'Categories', 'items' => $categories],
                    ['key' => 'type', 'title' => 'Types', 'items' => $types],
                ] as $group)
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ $group['title'] }}</h3>

                        <form class="mt-4 flex gap-2" method="post" action="{{ route('tickets.settings.store', $group['key']) }}">
                            @csrf
                            <input name="name" type="text" placeholder="Add new..."
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-500">
                                Add
                            </button>
                        </form>

                        <div class="mt-4 space-y-2">
                            @foreach($group['items'] as $item)
                                <div class="flex items-center justify-between border border-gray-200 rounded-md px-3 py-2">
                                    <span class="text-sm text-gray-700">{{ $item->name }}</span>
                                    <form method="post" action="{{ route('tickets.settings.destroy', [$group['key'], $item->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
                            @endforeach
                            @if($group['items']->isEmpty())
                                <p class="text-sm text-gray-500">No items yet.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
