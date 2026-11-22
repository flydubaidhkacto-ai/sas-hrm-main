@extends('layouts.app')

@section('title', 'Download ProHR Mobile App')

@section('content')
@php
$info = company_data();
$ui_setting_info = \App\Models\Setting::where('key', 'ui_setting')->first();
$ui_info = $ui_setting_info ? json_decode($ui_setting_info->value, true) : [];
$sidebar_color_left = $ui_info['navbar_background_color_left'] ?? 'rgb(244, 59, 72) 0%';
$sidebar_color_right = $ui_info['navbar_background_color_right'] ?? 'rgb(69, 58, 148) 100%';
$gradientColors = "$sidebar_color_left, $sidebar_color_right";
@endphp

<style>
    .download-btn {
        background: linear-gradient(to right, {{ $gradientColors }}) !important;
        border: none;
        padding: 15px 30px;
        border-radius: 8px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        transition: transform 0.2s;
    }

    .download-btn:hover {
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }

    .qr-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        margin: 20px 0;
    }

    .qr-code-container svg,
    .qr-code-container img {
        width: 100%
    }
    .qr-code-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        display: inline-block;
        border: 3px solid #e9ecef;
    }

    .instruction-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin: 15px 0;
    }

    .step-number {
        background: rgba(255,255,255,0.2);
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .auth-section {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        border-radius: 15px;
        padding: 25px;
        margin-top: 30px;
    }

    .generate-qr-btn {
        background: #28a745;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
    }

    .generate-qr-btn:hover {
        background: #218838;
    }

    #authQrCode {
        min-height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px dashed #dee2e6;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    .status-indicator {
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: bold;
        text-align: center;
        margin: 10px 0;
    }

    .status-waiting {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-expired {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<div class="main-wrapper">
    <div class="account-content">
        <div class="container">
            <!-- Header -->
            <div class="account-logo">
                <a href="{{ route('home') }}">
                    <img src="{{ ($info['logo'] ?? false) ? '/uploaded_files/' . $info['logo'] : 'https://app.prohrbd.com/assets/img/logo.jpg' }}" alt="ProHR Logo">
                </a>
            </div>

            <div class="text-center mb-4">
                <h2 class="mb-3">Download ProHR Mobile App</h2>
                <p class="text-muted">Get the mobile app and login instantly with QR code</p>
            </div>

            <!-- APK Download Section -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="qr-section text-center">
                        <div class="row">
                            <!-- Download Section -->
                            <div class="col-md-6">
                                <h4 class="mb-4"><i class="fa fa-download text-primary"></i> Download Mobile App</h4>
                                <div class="mb-4">
                                    <i class="fa fa-android" style="font-size: 60px; color: #3DDC84;"></i>
                                </div>
                                <p class="text-muted mb-4">Download the latest version of ProHR mobile application</p>
                                <a href="https://app.prohrbd.com/prohr.apk?v={{ config('app.version') }}" target="_blank" download="ProHR.apk" class="download-btn">
                                    <i class="fa fa-download me-2"></i> Download APK
                                </a>
                                <div class="mt-3">
                                    <small class="text-muted">Version: {{ config('app.version') }}</small>
                                </div>
                            </div>

                            <!-- QR Code Section -->
                            <div class="col-md-6">
                                <h4 class="mb-4"><i class="fa fa-qrcode text-success"></i> Quick Setup</h4>
                                <div class="qr-code-container mb-3">
                                    {!! $qrCodeSvg !!}
                                </div>
                                <p class="text-muted">
                                    <small>
                                        <strong>Quick Setup:</strong><br>
                                        Scan this QR code with your downloaded app to automatically configure your server URL
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="instruction-card">
                        <h4 class="mb-4"><i class="fa fa-list-ol me-2"></i> Setup Instructions</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="step-number">1</div>
                                    <div>Download the APK file and install it on your Android device</div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="step-number">2</div>
                                    <div>Open the installed ProHR app</div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="step-number">3</div>
                                    <div>Scan the QR code above for quick setup</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="step-number">4</div>
                                    <div>Or manually enter server URL: <code>{{ getSchemeAndHttpHost() }}</code></div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="step-number">5</div>
                                    <div>Use your email/phone and password to login</div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="step-number">6</div>
                                    <div>Or use QR code login (see below)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Code Authentication Section -->
            @auth
            <div class="row justify-content-center d-none">
                <div class="col-lg-10">
                    <div class="auth-section">
                        <h4 class="mb-4 text-dark">
                            <i class="fa fa-mobile-alt me-2"></i> QR Code Login
                        </h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-dark mb-4">
                                    Already have the app installed? Generate a QR code to login instantly without entering credentials.
                                </p>
                                <button id="generateQrBtn" class="generate-qr-btn mb-3">
                                    <i class="fa fa-qrcode me-2"></i> Generate Login QR Code
                                </button>
                                <div id="qrStatus"></div>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fa fa-info-circle me-1"></i>
                                        QR codes expire after 5 minutes for security
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="authQrCode">
                                    <div class="text-muted text-center">
                                        <i class="fa fa-qrcode" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2">Click "Generate Login QR Code" to create authentication QR</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="row justify-content-center d-none">
                <div class="col-lg-10">
                    <div class="auth-section">
                        <div class="text-center">
                            <h5 class="text-dark mb-3">
                                <i class="fa fa-sign-in-alt me-2"></i> Login Required for QR Authentication
                            </h5>
                            <p class="text-dark mb-4">
                                To use QR code login feature, please login to your account first.
                            </p>
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="fa fa-sign-in-alt me-2"></i> Login Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endauth

            <!-- Footer -->
            <div class="text-center mt-5">
                <p class="text-muted">
                    Developed by <a href="https://prosoft-bd.com" target="_blank">ProSoft</a>
                    v{{ config('app.version') }}
                </p>
            </div>
        </div>
    </div>
</div>

@auth
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentToken = null;
    let statusInterval = null;

    const generateBtn = document.getElementById('generateQrBtn');
    const qrCodeDiv = document.getElementById('authQrCode');
    const statusDiv = document.getElementById('qrStatus');

    generateBtn.addEventListener('click', function() {
        generateAuthQR();
    });

    function generateAuthQR() {
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Generating...';

        qrCodeDiv.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;

        fetch('{{ route('mobile-app.generate-qr') }}')
            .then(response => response.json())
            .then(data => {
                if (data.qr_code) {
                    currentToken = data.token;
                    qrCodeDiv.innerHTML = data.qr_code;

                    statusDiv.innerHTML = `
                        <div class="status-indicator status-waiting">
                            <i class="fa fa-clock me-2"></i> Waiting for mobile app to scan QR code...
                        </div>
                    `;

                    // Start polling for status
                    startStatusPolling();

                    // Set expiration timer
                    setTimeout(() => {
                        if (currentToken) {
                            stopStatusPolling();
                            showExpiredStatus();
                        }
                    }, 300000); // 5 minutes
                } else {
                    throw new Error('Failed to generate QR code');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                qrCodeDiv.innerHTML = `
                    <div class="text-danger">
                        <i class="fa fa-exclamation-triangle" style="font-size: 48px;"></i>
                        <p class="mt-2">Failed to generate QR code</p>
                    </div>
                `;
                statusDiv.innerHTML = `
                    <div class="status-indicator status-expired">
                        <i class="fa fa-times me-2"></i> Error generating QR code
                    </div>
                `;
            })
            .finally(() => {
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="fa fa-qrcode me-2"></i> Generate New QR Code';
            });
    }

    function startStatusPolling() {
        statusInterval = setInterval(() => {
            if (currentToken) {
                checkQRStatus();
            }
        }, 2000); // Poll every 2 seconds
    }

    function stopStatusPolling() {
        if (statusInterval) {
            clearInterval(statusInterval);
            statusInterval = null;
        }
    }

    function checkQRStatus() {
        fetch(`{{ route('mobile-app.qr-status') }}?token=${currentToken}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'used') {
                    stopStatusPolling();
                    statusDiv.innerHTML = `
                        <div class="status-indicator status-success">
                            <i class="fa fa-check-circle me-2"></i> Authentication successful! User logged in on mobile app.
                        </div>
                    `;
                    currentToken = null;
                } else if (data.status === 'expired') {
                    stopStatusPolling();
                    showExpiredStatus();
                }
                // 'waiting' status continues polling
            })
            .catch(error => {
                console.error('Status check error:', error);
            });
    }

    function showExpiredStatus() {
        statusDiv.innerHTML = `
            <div class="status-indicator status-expired">
                <i class="fa fa-hourglass-end me-2"></i> QR code has expired. Please generate a new one.
            </div>
        `;
        currentToken = null;
    }
});
</script>
@endauth
@endsection
