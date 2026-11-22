@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Device Sessions</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Device Sessions</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-danger" onclick="terminateOtherSessions()">
                        <i class="fa fa-power-off"></i> End All Other Sessions
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Active Device Sessions</h4>
                        <p class="text-muted small mb-0">Manage devices that are currently signed in to your account</p>
                    </div>
                    <div class="card-body">
                        <div id="sessions-container">
                            <!-- Sessions will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Session Item Template -->
<template id="session-template">
    <div class="session-item border rounded p-3 mb-3" data-session-id="">
        <div class="row align-items-center">
            <div class="col-md-1 text-center">
                <i class="device-icon fa-2x"></i>
            </div>
            <div class="col-md-6">
                <h6 class="mb-1 device-name"></h6>
                <div class="device-details text-muted small">
                    <span class="platform-browser"></span><br>
                    <span class="ip-address"></span><br>
                    <span class="created-date"></span>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="last-activity">
                    <strong class="activity-time"></strong><br>
                    <small class="text-muted activity-date"></small>
                </div>
            </div>
            <div class="col-md-2 text-right">
                <span class="current-badge badge badge-success d-none">Current</span>
                <button type="button" class="btn btn-danger btn-sm terminate-btn" onclick="terminateSession(this)">
                    <i class="fa fa-times"></i> End Session
                </button>
            </div>
        </div>
    </div>
</template>

@push('js')
<script>
$(document).ready(function() {
    loadSessions();

    // Auto-refresh sessions every 30 seconds
    setInterval(loadSessions, 30000);
});

function loadSessions() {
    $.get('{{ route("device-sessions.get") }}', function(response) {
        if (response.success) {
            renderSessions(response.sessions);
        }
    }).fail(function() {
        toastr.error('Failed to load device sessions');
    });
}

function renderSessions(sessions) {
    const container = $('#sessions-container');
    const template = $('#session-template').html();

    if (sessions.length === 0) {
        container.html(`
            <div class="text-center py-4">
                <i class="fa fa-mobile fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No active sessions found</h5>
                <p class="text-muted">You don't have any active device sessions.</p>
            </div>
        `);
        return;
    }

    container.empty();

    sessions.forEach(function(session) {
        const sessionEl = $(template);

        sessionEl.attr('data-session-id', session.id);
        sessionEl.find('.device-name').text(session.device_name || 'Unknown Device');
        sessionEl.find('.platform-browser').text(`${session.platform || 'Unknown'} • ${session.browser || 'Unknown Browser'}`);
        sessionEl.find('.ip-address').text(`IP: ${session.ip_address || 'Unknown'}`);
        sessionEl.find('.created-date').text(`First seen: ${session.created_at}`);
        sessionEl.find('.activity-time').text(session.last_activity);
        sessionEl.find('.activity-date').text(session.last_activity_date);

        // Set device icon
        let iconClass = 'fa fa-desktop text-primary';
        if (session.device_type === 'mobile') {
            iconClass = 'fa fa-mobile text-success';
        } else if (session.device_type === 'tablet') {
            iconClass = 'fa fa-tablet text-info';
        }
        sessionEl.find('.device-icon').addClass(iconClass);

        // Handle current session
        if (session.is_current) {
            sessionEl.find('.current-badge').removeClass('d-none');
            sessionEl.find('.terminate-btn').addClass('d-none');
            sessionEl.addClass('border-success bg-light');
        }

        container.append(sessionEl);
    });
}

function terminateSession(button) {
    const sessionItem = $(button).closest('.session-item');
    const sessionId = sessionItem.data('session-id');

    if (confirm('Are you sure you want to end this session?')) {
        $.post('{{ route("device-sessions.terminate") }}', {
            session_id: sessionId,
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                sessionItem.fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                toastr.error(response.message);
            }
        })
        .fail(function() {
            toastr.error('Failed to terminate session');
        });
    }
}

function terminateOtherSessions() {
    if (confirm('Are you sure you want to end all other sessions? You will remain signed in on this device.')) {
        $.post('{{ route("device-sessions.terminate-others") }}', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                loadSessions(); // Reload sessions
            } else {
                toastr.error(response.message);
            }
        })
        .fail(function() {
            toastr.error('Failed to terminate other sessions');
        });
    }
}
</script>
@endpush

@push('css')
<style>
.session-item {
    transition: all 0.3s ease;
}

.session-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.device-icon {
    opacity: 0.8;
}

.last-activity {
    text-align: center;
}

.current-badge {
    font-size: 0.7rem;
}

@media (max-width: 768px) {
    .session-item .row > div {
        margin-bottom: 10px;
        text-align: center !important;
    }

    .session-item .col-md-2 {
        text-align: center !important;
    }
}
</style>
@endpush

@endsection
