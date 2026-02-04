@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:bg-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>
