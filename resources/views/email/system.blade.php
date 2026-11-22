<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? 'System Notification' }}</title>
</head>

<body style="margin:0; padding:20px; background:#f5f6fa; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="left">

            {{-- Main Container --}}
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#ffffff; border-radius:8px; border:1px solid #e0e0e0;">

                {{-- Content Wrapper --}}
                <tr>
                    <td style="padding:30px;">

                        @if(!empty($body))
                        <p style="margin:0; font-size:15px; color:#333; line-height:1.7;">
                            {!! $body !!}
                        </p>
                        @endif

                        @if(!empty($status))
                            <div style="
                                    margin-top:10px;
                                    padding:14px;
                                    text-transform: capitalize;
                                    background:{{ $statusBg ?? '#f5f6fa' }};
                                    border-left:5px solid {{ $statusColor ?? '#ccc' }};
                                    font-size:15px;
                                    color:#333;">
                                <strong>Status:</strong>
                                <span style="color:{{ $statusColor ?? '#333' }}; font-weight:bold;">
                                {{ $status ?? 'N/A' }}
                            </span>
                            </div>
                        @endif

                        {{-- Button --}}
                        @if(!empty($url))
                            <div style="margin-top:22px;">
                                <a href="{{ $url }}"
                                   style="
                                           background:{{ $buttonColor ?? '#1e90ff' }};
                                           color:#ffffff;
                                           padding:14px 28px;
                                           border-radius:6px;
                                           text-decoration:none;
                                           font-weight:bold;
                                           font-size:15px;
                                           display:inline-block;">
                                    {{ $buttonText ?? 'View Details' }}
                                </a>
                            </div>
                        @endif

                        {{-- Footer --}}
                        <div style="margin-top:30px; border-top:1px solid #eee; padding-top:15px;
                                    font-size:12px; color:#888;">
                            This is an automated message from
                            <strong>{{ config('app.name') }}</strong>.<br>
                            Please do not reply to this email.
                        </div>

                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

</body>
</html>
