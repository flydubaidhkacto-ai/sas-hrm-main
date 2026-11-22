@extends('layouts.master')
@section('title', "Create Salary Grade ")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        Create Salary Grade
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('grade_system')}}">Salary Grade </a></li>
                        <li class="breadcrumb-item active">
                            Create Salary Grade
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <form class="col-md-12" action="{{ route('grade_system.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label for="">Name:</label>
                        <input name="name" type="text" class="form-control" value="{{old('name')}}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="">Amount:</label>
                        <input name="amount" type="number" class="form-control" value="{{ old('amount') ?? '00' }}">
                    </div>
                </div>
                <div class="mt-2">
                    <button class="btn btn-primary">
                        Create
                    </button>
                </div>
        </div>
    </div>
</div>
<!-- /Page Content -->
</div>

<!-- /Page Wrapper -->
@section('script')
{{-- <script>
    function show_hide_not_allowance(val)
        {
            return  $('.hide').removeClass('d-none');
            if(val.value == 'basic' || val.value == 'rewards')
            {
                $('.hide').addClass('d-none');
                console.log(222);
            }
            else {
                $('.hide').removeClass('d-none');
            }
        }
        show_hide_not_allowance($('[name=category]')[0])
    ;
</script> --}}
@endsection
@endsection