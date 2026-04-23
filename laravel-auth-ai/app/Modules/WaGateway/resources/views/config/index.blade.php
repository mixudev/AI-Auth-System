@extends('layouts.app-dashboard')

@section('title', 'WhatsApp Gateway')
@section('page-title', 'WhatsApp Gateway')

@section('content')
<div class="space-y-6" x-data="{ tab: 'gateways' }">
    {{-- Toolbar & Tab Selection --}}
    @include('wa-gateway::config.partials.tabs')

    <div class="mt-6">
        <template x-if="tab === 'gateways'">
            <div class="space-y-6">
                @include('wa-gateway::config.partials.stats')
                @include('wa-gateway::config.partials.connection_hub')
            </div>
        </template>

        <template x-if="tab === 'templates'">
            @include('wa-gateway::config.partials.templates_tab')
        </template>

        <template x-if="tab === 'logs'">
            <div class="space-y-6">
                @include('wa-gateway::config.partials.logs_table')
            </div>
        </template>
    </div>
</div>

{{-- Modals --}}
@include('wa-gateway::config.partials.modals')

@push('scripts')
    {{-- Main Logic --}}
    @include('wa-gateway::config.partials.scripts')
@endpush

@endsection
