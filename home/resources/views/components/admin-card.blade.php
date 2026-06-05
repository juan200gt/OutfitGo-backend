@props(['title'])
<div class="bg-white p-6 rounded-lg shadow-md mb-8 border-t-4 border-blue-500">
    <h2 class="text-xl font-bold mb-4">{{ $title }}</h2>
    <div>
        {{ $slot }}
    </div>
</div>
