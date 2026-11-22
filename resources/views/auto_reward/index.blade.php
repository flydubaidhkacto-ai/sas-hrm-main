@extends('layouts.master')
@section('title', "Create Reward")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper" onmousemove="calculateDuration()">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Auto Reward Management
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('auto-rewards.index') }}">Auto Reward</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <form action="{{ route('auto-rewards.store') }}" method="POST">
                @csrf
                <div class="">
                   
                    <div class="row">   
                        <div class="col-sm-6 col-md-3">
                            <label for="">Reward Type</label>
                            <select name="type" class="form-control">
                                <option value="0" {{ old('type', $autoReward->type ?? '') == 0 ? 'selected' : '' }}>Percent</option>
                                <option value="1" {{ old('type', $autoReward->type ?? '') == 1 ? 'selected' : '' }}>Amount</option>
                            </select>                       
                        </div>
                        
                        <div class="mb-2">
                            <label for="">Amount: (Tk / Percent)</label>
                            <input type="text" name="amount" class="form-control" value="{{ old('amount', $autoReward->amount ?? '') }}">
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        {{ $autoReward ? 'Update' : 'Store' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>

    </script>
@endSection
