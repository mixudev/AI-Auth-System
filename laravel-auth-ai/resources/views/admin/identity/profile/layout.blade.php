@extends('layouts.app-dashboard')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

{{-- Base URL tanpa query string, digunakan oleh JS --}}
@php $profileBaseUrl = route('dashboard.profile.show'); @endphp

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div class="max-w-6xl mx-auto" id="profile-root" 
     x-data="{ 
        activePanel: '{{ $currentPanel ?? request()->input('panel', 'profile') }}',
        init() {
            @if(session('success'))
                setTimeout(() => window.AppPopup?.success({ title: 'Berhasil', description: '{{ session('success') }}' }), 100);
            @endif
            @if(session('error'))
                setTimeout(() => window.AppPopup?.error({ title: 'Gagal', description: '{{ session('error') }}' }), 100);
            @endif

            window.addEventListener('popstate', (e) => {
                this.activePanel = e.state?.panel || 'profile';
            });
            this.$watch('activePanel', (val) => {
                if (val === 'preferences') {
                    setTimeout(() => window.initOtpCards?.(), 10);
                }
            });
            if (this.activePanel === 'preferences') setTimeout(() => window.initOtpCards?.(), 50);
        },
        switchPanel(name) {
            if (this.activePanel === name) return;
            this.activePanel = name;
            const url = `{{ $profileBaseUrl }}?panel=${name}`;
            history.pushState({ panel: name }, '', url);
        }
     }">

    @include('identity::profile.partials.header')

    <!-- ════ TWO-COLUMN BODY ════ -->
    <div class="flex flex-col lg:flex-row gap-6">

        @include('identity::profile.partials.sidebar')

        <!-- Main Panel Area (Instant Toggling) -->
        <main class="flex-1 min-w-0" id="profile-panel">
            <div x-show="activePanel === 'profile'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @include('identity::profile.panels.profile')
            </div>
            <div x-show="activePanel === 'security'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @include('identity::profile.panels.security')
            </div>
            <div x-show="activePanel === 'preferences'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @include('identity::profile.panels.preferences')
            </div>
            <div x-show="activePanel === 'devices'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @include('identity::profile.panels.devices')
            </div>
            <div x-show="activePanel === 'activity'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @include('identity::profile.panels.activity')
            </div>
        </main>
    </div>

    @include('identity::profile.partials.modals')
</div>

@endsection

@include('identity::profile.partials.scripts')

