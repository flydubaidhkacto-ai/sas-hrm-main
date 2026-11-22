@extends('layouts.master')
@section('title', "Edit Deduction")
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
                        Edit Deduction: {{ $head->name }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('deductions.index')}}">Deduction</a></li>
                        <li class="breadcrumb-item active">
                            Edit Deduction
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <div class="row">
            <form class="col-md-12" action="{{ route('deductions.update', $head) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-1">
                        <label for="">Code:</label>
                        <input name="code" type="text" class="form-control" value="{{$head->code}}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="">Name:</label>
                        <input name="name" type="text" class="form-control" value="{{$head->name}}" required>
                    </div>
                    <div class="col-md-2">
                        <label for="">Amount:</label>
                        <input name="amount" type="number" step="any" class="form-control" required
                            value="{{ $head->amount ?? '00' }}">
                    </div>
                    <div class="col-md-2">
                        <label for="">Amount Type:</label>
                        <select name="amount_type" class="form-control select" required>
                            <option @if($head->amount_type == 'fixed')
                                selected
                                @endif value="fixed">Fixed Taka</option>
                            <option @if($head->amount_type == 'percentage')
                                selected
                                @endif value="percentage">Percentage</option>
                            <option @if($head->amount_type == 'other' || $head->amount_type == '')
                                selected
                                @endif value="other">Multiply</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="">Category:</label>
                        <select name="category" class="form-control select" required>
                            @foreach (\App\Models\Deduction::$categories as $k => $item)
                            <option @if($head->category === $k) selected @endif
                                value="{{ $k }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-2">
                    <label for="update_all">
                        <input type="checkbox" name="update_all" id="update_all">
                        Update All salary card deductions with the same code
                    </label>
                </div>
                <div class="mt-2">
                    <button class="btn btn-primary">
                        Update
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
