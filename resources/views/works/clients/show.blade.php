@extends('layouts.master')
@section('title', "Salary Card")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Salary card of: {{ $card->employee?->name }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll-cards') }}">Salary Cards</a></li>
                        <li class="breadcrumb-item active">
                            Card of: {{ $card->employee?->name }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        {!! Toastr::message() !!}
        <form class="">
            @csrf
            <div class="mb-2">
                <div class="row">
                    <div class="col-3">
                        <img src="/uploaded_files/{{ $card->employee?->picture_file }}" alt="" class="w-100">
                    </div>
                    <div class="col-9">
                        <h4>{{ $card->employee?->name }}</h4>
                        <p class="mb-0">
                            {{ $card->employee?->department?->super_department?->name }}:
                            {{ $card->employee?->department?->name }}
                        </p>
                        <p class="mb-0">{{ $card->employee?->designation?->name }}</p>
                        <p class="text-muted mb-0">{{ $card->employee?->email }}</p>
                        <p class="text-muted mb-0">{{ $card->employee?->phone }}</p>
                        <p class="text-muted mb-0">{{ $card->employee?->address }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="row" onkeyup="calc()" onchange="calc()" onmouseup="calc()">
                        <div class="col-md-6">
                            <h4>
                                Earn Heads:
                            </h4>
                            <div class="mt-1">
                                @if ($card->earn_heads->count())
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>
                                                Name
                                            </th>
                                            <th>
                                                Category
                                            </th>
                                            <th>
                                                Amount
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="heads">
                                        @foreach ($card->earn_heads as $i => $head)
                                        <tr class="head">
                                            <td>
                                                {{ $head->name }}
                                                <br>
                                                <i class="preview"></i>
                                            </td>
                                            <td>
                                                {{ \App\Models\EarnHead::$categories[$head->category] ?? '' }}
                                                <input type="hidden" class="category" value="{{ $head->category }}">
                                            </td>
                                            <td>
                                                @if ($head->category === 'rewards' || $head->category === 'emp_daily_expenses')
                                                <i>-</i>
                                                @else
                                                {{ $head->amount ?? '00' }}
                                                {{ $head->amount_type == 'fixed' ? 'Tk - Fixed' : ($head->amount_type ==
                                                'percentage' ? '%' : 'x') }}
                                                @endif
                                                <input type="hidden" class="amount_type"
                                                    value="{{ $head->amount_type }}">
                                                <input type="hidden" class="amount" value="{{ $head->amount }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <i>
                                    No Earn Head found! Please create one from
                                    <a href="{{ route('earn-head') }}">here</a>
                                </i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4>
                                Deductions:
                            </h4>
                            <div class="mt-1">
                                @if ($card->deductions->count())
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>
                                                Name
                                            </th>
                                            <th>
                                                Category
                                            </th>
                                            <th>
                                                Amount
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="deducts">
                                        @foreach ($card->deductions as $i => $head)
                                        <tr class="deduct">
                                            <td>
                                                {{ $head->name }}
                                                <br>
                                                <i class="preview"></i>
                                            </td>
                                            <td>
                                                {{ \App\Models\Deduction::$categories[$head->category] ?? '' }}
                                                <input type="hidden" class="category" value="{{ $head->category }}">
                                            </td>
                                            <td>
                                                @if($head->category === 'loan' || $head->category === 'fine')
                                                <i>Will be taken from system</i>
                                                @else
                                                {{ $head->amount ?? '00' }}
                                                {{ $head->amount_type == 'fixed' ? 'Tk - Fixed' : ($head->amount_type ==
                                                'percentage' ? '%' : 'x') }}
                                                <input type="hidden" class="amount_type"
                                                    value="{{ $head->amount_type }}">
                                                <input type="hidden" class="amount" value="{{ $head->amount }}">
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <i>
                                    No Deduction found! Please create one from
                                    <a href="{{ route('deductions') }}">here</a>
                                </i>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="">
                            <div class="d-inline-block" style="width: 200px">
                                <label for="">
                                    <b>Gross Salary:</b>
                                </label>
                                <input type="hidden" class="form-control salary" value="{{ $card->salary }}">
                                <span class="form-control">{{ $card->salary }} Tk</span>
                            </div>
                            <div class="d-inline-block " style="width: 200px">
                                <label for="">
                                    <b>Preview Salary:</b>
                                </label>
                                <span class="form-control total">{{ $card->salary }} Tk</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        @if (role_can('payroll_card') ||
                        role_can('payroll_card.create') ||
                        role_can('payroll_card.create.self.super_department'))
                        <a href="{{ route('payroll-cards.edit', $card) }}" class="btn btn-primary">
                            <i class="fa fa-edit"></i>
                            Edit
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
    .preview {
        color: gray;
        font-size: 10px;
    }
</style>
@endsection
@section('script')
<script>
    function calc() {
        let salary = parseFloat($('.salary').val() || 0)
        let total = 0
        $('.heads .head').each((i, element) => {
            let amount = parseFloat($(element).find('.amount').val() || 0)
            let amount_type = $(element).find('.amount_type').val() || 0
            let category = $(element).find('.category').val()

            if (category !== 'overtime' && category !== 'emp_daily_expenses')
            {
                let tmp = 0
                if (amount_type == 'fixed') {
                    tmp += (amount)
                } else
                if (amount_type == 'percentage') {
                    tmp += ((amount) / 100) * (salary)
                } else {
                    tmp += ((salary) * (amount))
                }
                $(element).find('.preview').html(tmp.toFixed(2) + ' Tk. (from preview)')
                total += tmp
            }
            if (category == 'basic') {
                $(element).find('.preview').html(salary.toFixed(2) + ' Tk.')
            }
        });
        $('.deducts .deduct').each((i, element) => {
            let amount = parseFloat($(element).find('.amount').val() || 0)
            let amount_type = $(element).find('.amount_type').val() || 0
            let category = $(element).find('.category').val()
            console.log(category);
            if (category !== 'late' && category !== 'fine' && category !== "loan") {
                let tmp = 0
                if (amount_type == 'fixed') {
                    tmp = (amount)
                } else
                if (amount_type == 'percentage') {
                    tmp = ((amount) / 100) * (salary)
                } else {
                    tmp = ((salary) * (amount))
                }
                $(element).find('.preview').html(tmp.toFixed(2) + ' Tk. (from preview)')
                total -= tmp
            }
        });
        $('span.total').html((total).toFixed(2) + ' Tk.')
    }
    calc()
</script>
@endSection
