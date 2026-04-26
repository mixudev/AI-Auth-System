<!-- Content Wrapper for AJAX -->
<div id="log-content-area" class="space-y-6">
    <!-- Stats (Auth Only) -->
    @include('AuditLog::Admin.partials._stats_auth')

    <!-- Toolbar (Search & Filter) -->
    @include('AuditLog::Admin.partials._toolbar')

    <!-- Data Table -->
    @if($tab === 'auth')
        @include('AuditLog::Admin.partials._table_auth')
    @else
        @include('AuditLog::Admin.partials._table_audit')
    @endif
</div>
