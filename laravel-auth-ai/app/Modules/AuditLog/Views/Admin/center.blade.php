@extends('layouts.app-dashboard')

@section('title', 'Log Monitoring Center')
@section('page-title', 'Log Monitoring Center')

@section('content')
<div class="space-y-6">
    <!-- Header & Tabs -->
    @include('AuditLog::Admin.partials._header')

    <!-- AUTH LOGS PANEL -->
    <div id="panel-auth" class="{{ $tab === 'auth' ? '' : 'hidden' }} space-y-6">
        @include('AuditLog::Admin.partials._stats_auth')
        @include('AuditLog::Admin.partials._toolbar', ['tab' => 'auth'])
        @include('AuditLog::Admin.partials._table_auth', ['logs' => $authLogs])
    </div>

    <!-- AUDIT LOGS PANEL -->
    <div id="panel-audit" class="{{ $tab === 'audit' ? '' : 'hidden' }} space-y-6">
        @include('AuditLog::Admin.partials._toolbar', ['tab' => 'audit'])
        @include('AuditLog::Admin.partials._table_audit', ['logs' => $auditLogs])
    </div>
</div>

<!-- Modals & Scripts -->
@include('AuditLog::Admin.partials._scripts')
@endsection
