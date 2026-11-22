@extends('vendor.mail.html.layout')

@section('content')
<tr>
    <td class="content-cell">
        <h1>Reset Password Notification</h1>
        <p>You are receiving this email because we received a password reset request for your account.</p>

        <!-- Action Button -->
        <table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="center">
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td align="center">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                                    <tr>
                                        <td>
                                            <a href="{{ url('/reset-password/'.$token) }}"
                                                style="background-color: #007bff; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none;"
                                                target="_blank" rel="noopener">Reset Password</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <p>This password reset link will expire in {{
            config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.</p>
        <p>If you did not request a password reset, no further action is required.</p>
        <p>Regards,<br>{{ $company_info['name'] ?? config('app.name') }}</p>

        <!-- Subcopy -->
        <table class="subcopy" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td>
                    <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into
                        your web browser:</p>
                    <p class="subcopy-link"><a href="{{ url('/reset-password/'.$token) }}" target="_blank"
                            rel="noopener">{{ url('/reset-password/'.$token) }}</a></p>
                </td>
            </tr>
        </table>
    </td>
</tr>
@endsection