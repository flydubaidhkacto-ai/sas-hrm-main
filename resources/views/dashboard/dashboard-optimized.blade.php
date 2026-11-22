@extends('layouts.master')

@push('style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<style>
    a.card {
        color: inherit;
    }

    a.card:hover {
        background: rgba(207, 207, 207, 0.815);
    }

    #close-notice {
        background-color: transparent;
        border: none;
        font-size: 1.2rem;
        color: #666;
        cursor: pointer;
    }

    #close-notice:hover {
        color: #ff4d4d;
    }

    .fc-day-grid-event {
        width: 100% !important;
        display: block !important;
        padding: 5px 0 !important;
        border-radius: 5px !important;
    }

    /* color section under calender */
    .legend-container {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .legend-circle {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }

    /* Skeleton loading styles */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    .skeleton-text {
        height: 20px;
        border-radius: 4px;
        margin: 5px 0;
    }

    .skeleton-card {
        height: 100px;
        border-radius: 8px;
        margin: 10px 0;
    }

    .skeleton-title {
        height: 24px;
        width: 60%;
        border-radius: 4px;
        margin: 10px 0;
    }

    .skeleton-number {
        height: 36px;
        width: 80px;
        border-radius: 4px;
        margin: 10px 0;
    }

    /* Loading overlay */
    .loading-overlay {
        position: relative;
    }

    .loading-overlay::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 10;
        border-radius: 8px;
    }

    .loading-overlay::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid #007bff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s linear infinite;
        z-index: 11;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .error-state {
        text-align: center;
        color: #dc3545;
        padding: 20px;
    }

    .retry-btn {
        margin-top: 10px;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-md-9">
                    <h3 class="page-title">
                        Welcome {{ user()->name }}!
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ul>
                </div>
                <div class="col-md-3 col-sm-6">
                    <form action="" method="get" class="text-right">
                        @csrf
                        <b>Auto refresh data?</b>
                        <select name="home_page_refresh" id="auto-refresh-toggle"
                            onchange="$(this).next().next().removeClass('d-none')">
                            <option @if ($home_page_refresh) selected @endif value="1">Yes</option>
                            <option @if (!$home_page_refresh) selected @endif value="0">No</option>
                        </select>
                        <br>
                        <button class="btn btn-sm btn-info d-none">
                            Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Birthday Card Section -->
        <div id="birthday-section" class="mb-3">
            <!-- Dynamic content will be loaded here -->
        </div>

        <!-- Admin Cards Section -->
        <div id="admin-cards-section" class="mb-3">
            <!-- Skeleton loading for admin cards -->
            <div class="skeleton-loading">
                <hr>
                <div class="row">
                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12">
                        <div class="row">
                            @for ($i = 0; $i < 6; $i++) <div class="col-md-6 col-sm-6 col-lg-6 col-xl-4">
                                <div class="card dash-widget">
                                    <div class="card-body">
                                        <div class="skeleton skeleton-title"></div>
                                        <div class="skeleton skeleton-number"></div>
                                        <div class="skeleton skeleton-text"></div>
                                    </div>
                                </div>
                        </div>
                        @endfor
                    </div>
                </div>
                <div class="col-lg-4 col-xl-4 col-md-4 col-sm-12">
                    <div class="card dash-widget">
                        <div class="card-body" style="min-height: 250px;">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Machine Punches Section -->
            <div id="machine-punches-section">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Latest Machine punches:</h4>
                        <div class="skeleton-loading">
                            @for ($i = 0; $i < 5; $i++) <div class="skeleton skeleton-text">
                        </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <!-- Calendar Section -->
        <div class="row">
            <div class="card dash-widget">
                <h4 class="card-header">Official Calendar</h4>
                <div class="card-body">
                    <div id="calendar" class="mx-auto"></div>
                    <!-- Legend -->
                    <div class="mt-3 legend-container">
                        <div class="legend-item">
                            <span class="legend-circle" style="background-color: #FF0000;"></span>
                            <span>Holiday</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-circle" style="background-color: #ff5733;"></span>
                            <span>Roster</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-circle" style="background-color: #3357ff;"></span>
                            <span>Shift Schedule</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-circle" style="background-color: #28a745;"></span>
                            <span>General Working Day</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script type="module" src="{{ asset('assets/plugins/dynamic-event-calendar-bootstrap/js/main.js') }}"></script>
<script src="{{ asset('assets/js/confetti.min.js') }}"></script>
@endpush

@section('script')
<script>
    // Dashboard AJAX Manager
        class DashboardAjaxManager {
            constructor() {
                this.autoRefreshEnabled = {{ $home_page_refresh ? 'true' : 'false' }};
                this.refreshInterval = null;
                this.loadingStates = {};

                this.init();
            }

            init() {
                this.setupEventListeners();
                this.loadAllSections();
                this.initCalendar();

                if (this.autoRefreshEnabled) {
                    this.startAutoRefresh();
                }
            }

            setupEventListeners() {
                // Auto refresh toggle
                $('#auto-refresh-toggle').on('change', (e) => {
                    this.autoRefreshEnabled = e.target.value === '1';
                    if (this.autoRefreshEnabled) {
                        this.startAutoRefresh();
                    } else {
                        this.stopAutoRefresh();
                    }
                });

                // Visibility change handler
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible' && this.autoRefreshEnabled) {
                        this.startAutoRefresh();
                    } else {
                        this.stopAutoRefresh();
                    }
                });
            }

            showLoading(sectionId) {
                this.loadingStates[sectionId] = true;
                $(`#${sectionId}`).addClass('loading-overlay');
            }

            hideLoading(sectionId) {
                this.loadingStates[sectionId] = false;
                $(`#${sectionId}`).removeClass('loading-overlay');
            }

            showError(sectionId, message = 'Failed to load data') {
                const errorHtml = `
                    <div class="error-state">
                        <i class="fa fa-exclamation-triangle fa-2x"></i>
                        <p>${message}</p>
                        <button class="btn btn-sm btn-primary retry-btn" onclick="dashboardManager.retrySection('${sectionId}')">
                            <i class="fa fa-refresh"></i> Retry
                        </button>
                    </div>
                `;
                $(`#${sectionId} .skeleton-loading, #${sectionId} .card-body`).first().html(errorHtml);
            }

            retrySection(sectionId) {
                switch(sectionId) {
                    case 'admin-cards-section':
                        this.loadAdminCards();
                        break;
                    case 'machine-punches-section':
                        this.loadMachinePunches();
                        break;
                }
            }

            async loadAllSections() {
                // Load all sections in parallel for better performance
                const promises = [
                    this.loadAdminCards(),
                    this.loadMachinePunches(),
                ];

                try {
                    await Promise.allSettled(promises);
                } catch (error) {
                    console.error('Error loading dashboard sections:', error);
                }
            }

            async loadAdminCards() {
                try {
                    this.showLoading('admin-cards-section');

                    const response = await fetch('{{ route("ajax.admin-cards") }}', {
                        method: 'GET',
                        cache: 'no-store',
                        headers: {
                            'Accept': 'application/json, text/html, *',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }

                    const data = await response.json();

                    if (data.admin_cards || data.birthday_card) {
                        $('#admin-cards-section .skeleton-loading').replaceWith(data.admin_cards || '');
                        $('#birthday-section').html(data.birthday_card || '');
                    } else if (data.error) {
                        throw new Error(data.error);
                    }
                } catch (error) {
                    console.error('Error loading admin cards:', error);
                    this.showError('admin-cards-section', error.message || 'Failed to load employee statistics');
                } finally {
                    this.hideLoading('admin-cards-section');
                }
            }

            async loadMachinePunches() {
                try {
                    this.showLoading('machine-punches-section');

                    const response = await fetch('{{ route("ajax.machine-punches") }}', {
                        method: 'GET',
                        cache: 'no-store',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }

                    const data = await response.json();

                    if (data.html) {
                        $('#machine-punches-section .skeleton-loading').replaceWith(data.html);

                        // Add "See Machine logs" link
                        const seeMoreLink = `
                            <div class="load-more mt-2 text-center">
                                <a class="text-dark" href="{{ route('reports.machine-log') }}">See Machine logs</a>
                            </div>
                        `;
                        if($('.load-more').length === 0) {
                            $('#machine-punches-section .card-body').append(seeMoreLink);
                        }
                    } else if (data.error) {
                        throw new Error(data.error);
                    }
                } catch (error) {
                    console.error('Error loading machine punches:', error);
                    this.showError('machine-punches-section', error.message || 'Failed to load machine punches');
                } finally {
                    this.hideLoading('machine-punches-section');
                }
            }

            initCalendar() {
                const calendarUrl = "{{ route('calendar') }}";
                const currentDate = new Date();
                const month = currentDate.getMonth() + 1;
                const year = currentDate.getFullYear();

                $('#calendar').fullCalendar({
                    events: calendarUrl + "?month=" + month + "&year=" + year,
                    displayEventTime: false,
                    editable: false,
                    selectable: false,
                    eventRender: function(event, element) {
                        if (event.color) {
                            element.css('background-color', event.color);
                            element.css('border-color', event.color);
                        }
                        element.find('.fc-title').remove();
                    }
                });
            }

            startAutoRefresh() {
                this.stopAutoRefresh(); // Clear any existing interval

                this.refreshInterval = setInterval(() => {
                    if (document.visibilityState === 'visible') {
                        this.loadAllSections();
                    }
                }, 10_000); // Refresh every 30 seconds instead of full page reload
            }

            stopAutoRefresh() {
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                    this.refreshInterval = null;
                }
            }
        }

        // Initialize dashboard manager when document is ready
        let dashboardManager;
        $(document).ready(function() {
            dashboardManager = new DashboardAjaxManager();
        });
</script>
@endsection
