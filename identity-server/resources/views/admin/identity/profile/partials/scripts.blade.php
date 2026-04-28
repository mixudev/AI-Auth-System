@push('scripts')
    <script>
        (function () {
        'use strict';

        const CSRF_TOKEN = '{{ csrf_token() }}';

        @include('identity::profile.partials.scripts.ui')
        @include('identity::profile.partials.scripts.actions')
        @include('identity::profile.partials.scripts.mfa')
        @include('identity::profile.partials.scripts.devices')

        })();
    </script>
@endpush
