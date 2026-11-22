@php
$info = company_data();
@endphp
<!---
Developer: Md Ariful Islam
Company: ArifDev & ProSoft
Version: {{ config('app.version') }}
Location: Dhaka 1216, Bangladesh
For Support:
https://prosoft-bd.com
--->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <meta name="description" content="HR & Payroll System for Employee Management">
    <meta name="keywords"
        content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects"
    >
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'ProHR - ' . config('app.version'))</title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap.min.css?v=' . config('app.version')) }}">
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/font-awesome.min.css?v=' . config('app.version')) }}">
    <!-- Lineawesome CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/line-awesome.min.css?v=' . config('app.version')) }}">
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/select2.min.css?v=' . config('app.version')) }}">
    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap-datetimepicker.min.css?v=' . config('app.version')) }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/style.css?v=' . config('app.version')) }}">
    {{-- message toastr --}}
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css?v=' . config('app.version')) }}">
    <script src="{{ URL::to('assets/js/toastr_jquery.min.js?v=' . config('app.version')) }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js?v=' . config('app.version')) }}"></script>
</head>

<body class="account-page error-page" style="background-color: rgba(59, 62, 244, 0.2)">
    <style>
        .invalid-feedback {
            font-size: 14px;
        }
    </style>
    <!-- Main Wrapper -->
    @yield('content')
    <!-- /Main Wrapper -->
    <!-- jQuery -->
    <script src="{{ URL::to('assets/js/jquery-3.5.1.min.js') }}"></script>
    <!-- Bootstrap Core JS -->
    <script src="{{ URL::to('assets/js/popper.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>
    <!-- Slimscroll JS -->
    <script src="{{ URL::to('assets/js/jquery.slimscroll.min.js') }}"></script>
    <!-- Select2 JS -->
    <script src="{{ URL::to('assets/js/select2.min.js') }}"></script>
    <!-- Datetimepicker JS -->
    <script src="{{ URL::to('assets/js/moment.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <!-- Custom JS -->
    <script src="{{ URL::to('assets/js/app.js?v=1') }}"></script>
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
    @yield('script')
</body>

</html>
