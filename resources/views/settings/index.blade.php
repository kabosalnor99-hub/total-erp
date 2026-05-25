{{-- المسار: resources/views/settings/index.blade.php --}}

@extends('layouts.app')

@section('title', __('settings.title'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Page Header --}}
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-teal-500/30">
                        <x-heroicon-o-cog-6-tooth class="w-7 h-7 text-white"/>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">{{ __('settings.title') }}</h1>
                        <p class="text-sm text-slate-500 mt-1">{{ __('settings.subtitle') }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    @can('settings.backup')
                    <form action="{{ route('settings.backup') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 text-sm font-medium shadow-lg shadow-blue-500/30 transition-all duration-200 hover:scale-105"
                            onclick="return confirm('{{ __('settings.confirm_backup') }}')">
                            <x-heroicon-o-circle-stack class="w-4 h-4"/>
                            {{ __('settings.backup_now') }}
                        </button>
                    </form>
                    <form action="{{ route('settings.clear-cache') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-xl hover:from-amber-600 hover:to-amber-700 text-sm font-medium shadow-lg shadow-amber-500/30 transition-all duration-200 hover:scale-105">
                            <x-heroicon-o-arrow-path class="w-4 h-4"/>
                            {{ __('settings.clear_cache') }}
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-gradient-to-r from-emerald-50 to-emerald-100 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <x-heroicon-o-check-circle class="w-5 h-5 text-white"/>
            </div>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-red-100 border border-red-200 text-red-800 rounded-xl flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <x-heroicon-o-x-circle class="w-5 h-5 text-white"/>
            </div>
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div x-data="{ activeTab: '{{ array_key_first($groups) }}' }">

        {{-- Tab Nav --}}
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-2 mb-6">
            <nav class="flex gap-2 overflow-x-auto">
                @foreach($groups as $key => $label)
                <button
                    @click="activeTab = '{{ $key }}'"
                    :class="activeTab === '{{ $key }}'
                        ? 'bg-gradient-to-r from-teal-500 to-teal-600 text-white shadow-lg shadow-teal-500/30'
                        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800'"
                    class="px-5 py-2.5 text-sm font-medium rounded-xl whitespace-nowrap transition-all duration-200">
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

                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-800">{{ $groupLabel }}</h2>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($settings[$groupKey] as $setting)
                        @if($setting->is_editable)
                        <div class="px-6 py-5 flex items-start gap-4 hover:bg-slate-50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <label for="setting_{{ $setting->key }}" class="block text-sm font-semibold text-slate-800 mb-1">
                                    {{ $setting->label }}
                                </label>
                                @if($setting->description)
                                <p class="text-xs text-slate-500 mb-3">{{ $setting->description }}</p>
                                @endif

                                {{-- Render input based on type --}}
                                @if($setting->type === 'boolean')
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                            name="{{ $setting->key }}"
                                            id="setting_{{ $setting->key }}"
                                            class="sr-only peer"
                                            {{ $setting->typed_value ? 'checked' : '' }}>
                                        <div class="w-12 h-7 bg-slate-200 peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:bg-gradient-to-r peer-checked:from-teal-500 peer-checked:to-teal-600 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:after:translate-x-full shadow-sm"></div>
                                    </label>

                                @elseif($setting->type === 'file')
                                    @if($setting->value)
                                    <div class="mb-3">
                                        <img src="{{ Storage::url($setting->value) }}"
                                            alt="{{ $setting->label }}"
                                            class="h-16 w-auto rounded-xl border-2 border-slate-200 shadow-sm">
                                    </div>
                                    @endif
                                    <input type="file"
                                        name="{{ $setting->key }}"
                                        id="setting_{{ $setting->key }}"
                                        class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-gradient-to-r file:from-teal-50 file:to-teal-100 file:text-teal-700 file:font-medium hover:file:from-teal-100 hover:file:to-teal-200 transition-all">

                                @elseif($setting->type === 'integer')
                                    <input type="number"
                                        name="{{ $setting->key }}"
                                        id="setting_{{ $setting->key }}"
                                        value="{{ old($setting->key, $setting->value) }}"
                                        class="w-48 px-4 py-2.5 text-sm border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-teal-300 focus:border-teal-500 transition-all">

                                @elseif($setting->type === 'json')
                                    <textarea
                                        name="{{ $setting->key }}"
                                        id="setting_{{ $setting->key }}"
                                        rows="4"
                                        class="w-full px-4 py-2.5 text-sm border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-teal-300 focus:border-teal-500 font-mono transition-all">{{ old($setting->key, $setting->value) }}</textarea>

                                @else
                                    <input type="text"
                                        name="{{ $setting->key }}"
                                        id="setting_{{ $setting->key }}"
                                        value="{{ old($setting->key, $setting->value) }}"
                                        class="w-full max-w-lg px-4 py-2.5 text-sm border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-teal-300 focus:border-teal-500 transition-all">
                                @endif
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>

                    <div class="px-6 py-5 bg-gradient-to-r from-slate-50 to-slate-100 border-t border-slate-200 flex justify-end">
                        @can('settings.edit')
                        <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-teal-500 to-teal-600 text-white text-sm font-medium rounded-xl hover:from-teal-600 hover:to-teal-700 shadow-lg shadow-teal-500/30 transition-all duration-200 hover:scale-105">
                            {{ __('common.save_changes') }}
                        </button>
                        @endcan
                    </div>
                </div>
            </form>
            @else
            <div class="text-center py-16 bg-white rounded-2xl shadow-lg border border-slate-200">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-cog-6-tooth class="w-8 h-8 text-slate-400"/>
                </div>
                <p class="text-sm text-slate-500">{{ __('settings.no_settings_in_group') }}</p>
            </div>
            @endif
        </div>
        @endforeach

    </div>
</div>
@endsection
