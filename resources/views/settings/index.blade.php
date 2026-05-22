{{-- المسار: resources/views/settings/index.blade.php --}}

@extends('layouts.app')

@section('title', __('settings.title'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('settings.title') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('settings.subtitle') }}</p>
        </div>
        <div class="flex gap-2">
            @can('settings.backup')
            <form action="{{ route('settings.backup') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
                    onclick="return confirm('{{ __('settings.confirm_backup') }}')">
                    <x-heroicon-o-circle-stack class="w-4 h-4"/>
                    {{ __('settings.backup_now') }}
                </button>
            </form>
            <form action="{{ route('settings.clear-cache') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm">
                    <x-heroicon-o-arrow-path class="w-4 h-4"/>
                    {{ __('settings.clear_cache') }}
                </button>
            </form>
            @endcan
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2">
            <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0"/>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center gap-2">
            <x-heroicon-o-x-circle class="w-5 h-5 flex-shrink-0"/>
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div x-data="{ activeTab: '{{ array_key_first($groups) }}' }">

        {{-- Tab Nav --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-1 overflow-x-auto pb-1">
                @foreach($groups as $key => $label)
                <button
                    @click="activeTab = '{{ $key }}'"
                    :class="activeTab === '{{ $key }}'
                        ? 'border-b-2 border-teal-600 text-teal-700 bg-teal-50'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="px-4 py-2 text-sm font-medium rounded-t-lg whitespace-nowrap transition-colors">
                    {{ $label }}
                </button>
                @endforeach
            </nav>
        </div>

        {{-- Tab Panels --}}
        @foreach($groups as $groupKey => $groupLabel)
        <div x-show="activeTab === '{{ $groupKey }}'" x-cloak>
            @if(isset($settings[$groupKey]) && $settings[$groupKey]->isNotEmpty())
            <form action="{{ route('settings.update-group', $groupKey) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-base font-semibold text-gray-700">{{ $groupLabel }}</h2>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($settings[$groupKey] as $setting)
                        @if($setting->is_editable)
                        <div class="px-6 py-4 flex items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <label for="setting_{{ $setting->key }}" class="block text-sm font-medium text-gray-800 mb-0.5">
                                    {{ $setting->label }}
                                </label>
                                @if($setting->description)
                                <p class="text-xs text-gray-500 mb-2">{{ $setting->description }}</p>
                                @endif

                                {{-- Render input based on type --}}
                                @if($setting->type === 'boolean')
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                            name="{{ $setting->key }}"
                                            id="setting_{{ $setting->key }}"
                                            class="sr-only peer"
                                            {{ $setting->typed_value ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-teal-300 rounded-full peer peer-checked:bg-teal-600 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                                    </label>

                                @elseif($setting->type === 'file')
                                    @if($setting->value)
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($setting->value) }}"
                                            alt="{{ $setting->label }}"
                                            class="h-12 w-auto rounded border border-gray-200">
                                    </div>
                                    @endif
                                    <input type="file"
                                        name="{{ $setting->key }}"
                                        id="setting_{{ $setting->key }}"
                                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">

                                @elseif($setting->type === 'integer')
                                    <input type="number"
                                        name="{{ $setting->key }}"
                                        id="setting_{{ $setting->key }}"
                                        value="{{ old($setting->key, $setting->value) }}"
                                        class="w-40 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">

                                @elseif($setting->type === 'json')
                                    <textarea
                                        name="{{ $setting->key }}"
                                        id="setting_{{ $setting->key }}"
                                        rows="4"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 font-mono">{{ old($setting->key, $setting->value) }}</textarea>

                                @else
                                    <input type="text"
                                        name="{{ $setting->key }}"
                                        id="setting_{{ $setting->key }}"
                                        value="{{ old($setting->key, $setting->value) }}"
                                        class="w-full max-w-lg px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                @endif
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                        @can('settings.edit')
                        <button type="submit"
                            class="px-5 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 transition-colors">
                            {{ __('common.save_changes') }}
                        </button>
                        @endcan
                    </div>
                </div>
            </form>
            @else
            <div class="text-center py-12 text-gray-400">
                <x-heroicon-o-cog-6-tooth class="w-12 h-12 mx-auto mb-2 opacity-40"/>
                <p class="text-sm">{{ __('settings.no_settings_in_group') }}</p>
            </div>
            @endif
        </div>
        @endforeach

    </div>
</div>
@endsection
