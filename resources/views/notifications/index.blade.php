{{-- المسار: resources/views/notifications/index.blade.php --}}
@extends('layouts.app')
@section('title', __('notifications.title'))
@section('content')
<div class="p-6" dir="rtl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ __('notifications.title') }}</h1>
        <div class="flex gap-2">
            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    {{ __('notifications.mark_all_read') }}
                </button>
            </form>
            <form action="{{ route('notifications.clear-read') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    {{ __('notifications.clear_read') }}
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-100">
        @forelse($notifications as $notification)
        <div class="flex items-start gap-4 p-4 {{ $notification->is_read ? 'opacity-60' : 'bg-blue-50/30' }}">
            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 {{ $notification->color_class }}">
                <x-heroicon-o-bell class="w-4 h-4"/>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800">{{ $notification->title }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $notification->body }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                @if(!$notification->is_read)
                <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-teal-600 hover:underline">
                        {{ __('notifications.mark_read') }}
                    </button>
                </form>
                @endif
                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:underline">
                        {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-16 text-gray-400">
            <x-heroicon-o-bell class="w-12 h-12 mx-auto mb-2 opacity-40"/>
            <p class="text-sm">{{ __('notifications.empty') }}</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
