var CSRF = '{{ csrf_token() }}';

// ── Routes ────────────────────────────────────────────────────────────────
var ROUTES = {
    store    : '{{ route("dashboard.users.store") }}',
    update   : function(id) { return '{{ url("dashboard/users") }}/' + id; },
    destroy  : function(id) { return '{{ url("dashboard/users") }}/' + id; },
    block    : function(id) { return '{{ url("dashboard/users") }}/' + id + '/block'; },
    unblock  : function(id) { return '{{ url("dashboard/users") }}/' + id + '/unblock'; },
    resetPwd : function(id) { return '{{ url("dashboard/users") }}/' + id + '/reset-password'; },
    bulk     : '{{ route("dashboard.users.bulk") }}',
    verifyEmail: function(id) { return '{{ url("email/verification-notification") }}/' + id; }
};

// ── API helper ────────────────────────────────────────────────────────────
function api(method, url, data) {
    var opts = {
        method  : method,
        headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    };
    if (data) opts.body = JSON.stringify(data);
    return fetch(url, opts).then(function(r){ 
        if (r.status === 422) {
            return r.json().then(function(data) {
                var firstError = data.message;
                if (data.errors) {
                    var keys = Object.keys(data.errors);
                    if (keys.length > 0) firstError = data.errors[keys[0]][0];
                }
                return { success: false, message: firstError };
            });
        }
        return r.json(); 
    });
}
