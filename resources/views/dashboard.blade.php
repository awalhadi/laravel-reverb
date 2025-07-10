<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat') }}
        </h2>
    </x-slot>

    <div class="h-screen flex flex-col p-4">
        <div class="flex-1 overflow-hidden">
            <chat-component :current-user="{{ auth()->user() }}" />
        </div>
    </div>
</x-app-layout>
