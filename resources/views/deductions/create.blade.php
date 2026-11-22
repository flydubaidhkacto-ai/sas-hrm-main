@extends('layouts.master')
@section('title', "Create Deduction")
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
                        Create Deduction
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('deductions.index')}}">Deduction</a></li>
                        <li class="breadcrumb-item active">
                            Create Deduction
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <form class="col-md-12" action="{{ route('deductions.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-1">
                        <label for="">Code:</label>
                        <input name="code" type="text" class="form-control" value="{{old('code')}}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="">Name:</label>
                        <input name="name" type="text" class="form-control" value="{{old('name')}}" required>
                    </div>
                    <div class="col-md-2">
                        <label for="">Amount:</label>
                        <input name="amount" type="number" step="any" class="form-control" required
                            value="{{ old('amount') ?? '00' }}">
                    </div>
                    <div class="col-md-2">
                        <label for="">Amount Type:</label>
                        <select name="amount_type" class="form-control select" required>
                            <option @if(old('amount_type')=='fixed' ) selected @endif value="fixed">Fixed Taka</option>
                            <option @if(old('amount_type')=='percentage' ) selected @endif value="percentage">Percentage
                            </option>
                            <option @if(old('amount_type')=='other' ) selected @endif value="other">Multiply</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="">Category:</label>
                        <select name="category" class="form-control select" required>
                            @foreach (\App\Models\Deduction::$categories as $k => $item)
                            <option @if(old('category')===$k) selected @endif value="{{ $k }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-2">
                    <button class="btn btn-primary">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Page Content -->
</div>

<!-- /Page Wrapper -->
@section('script')
<script>
</script>
@endsection
@endsection
