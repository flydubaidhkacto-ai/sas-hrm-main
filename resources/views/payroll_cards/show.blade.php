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
                        <li class="breadcrumb-item"><a href="{{ route('payroll-cards.index') }}">Salary Cards</a></li>
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
                        <img src="/uploaded_files/{{ $card->employee?->picture_file }}" alt=""
                            style="width: 250px;"
                        >
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
                                @php
                                $earnHeads = $card->grade ? $card->grade->items : $card->earn_heads;
                                @endphp

                                @if ($earnHeads->count())
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
                                        @foreach ($earnHeads as $i => $head)
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
                                                @if ($head->category === 'rewards' || $head->category ===
                                                'emp_daily_expenses')
                                                <i>-</i>
                                                @else
                                                @if($card->grade && $head->category === 'basic')
                                                {{-- {{ $card->grade->amount }} TK --}}
                                                {{ $card->grade->items->first(fn ($item) => $item->category ==
                                                'basic')->amount ?: 0;}} TK
                                                <br>
                                                <span class="badge badge-info">
                                                    {{ $card->grade->name }}
                                                </span>
                                                @else
                                                {{ $head->amount ?? '00' }}
                                                {{ $head->amount_type == 'fixed'
                                                ? 'Tk - Fixed' : (
                                                $head->amount_type == 'percentage' ? '%' : 'x'
                                                )
                                                }}
                                                @endif
                                                @endif
                                                @if($card->grade && $head->category === 'basic')
                                                <input type="hidden" class="amount_type" value="fixed">
                                                <input type="hidden" class="amount" value="{{ $card->grade->amount }}">
                                                @else
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
                                    No Earn Head found! Please create one from
                                    <a href="{{ route('earn-head.index') }}">here</a>
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
                                                @php
                                                    $fine = null;
                                                    if ($head->category === 'fine') {
                                                        if ($head->reference_id === null || $head->reference_id === '') {
                                                            $fine = null;
                                                        } elseif ($head->reference_id > 0) {
                                                            $fine = $employee_fines->firstWhere('id', $head->reference_id);
                                                        }
                                                    }
                                                @endphp
                                                @if($head->category === 'loan')
                                                <i>Will be taken from system</i>
                                                @elseif($head->category === 'fine' && $head->reference_id === null)
                                                <i>Will be taken from system</i>
                                                @elseif($head->category === 'fine' && $head->reference_id == 0)
                                                {{ $head->amount ?? '00' }}
                                                {{ $head->amount_type == 'fixed' ? 'Tk - Fixed' : ($head->amount_type == 'percentage' ? '%' : 'x') }}
                                                <input type="hidden" class="amount_type" value="{{ $head->amount_type }}">
                                                <input type="hidden" class="amount" value="{{ $head->amount }}">
                                                @elseif($head->category === 'fine' && $fine)
                                                {{ $fine->reason ?? 'Fine #' . $fine->id }}
                                                (Remaining: {{ max(0, ($fine->amount ?? 0) - ($fine->paid_amount ?? 0)) }})
                                                @else
                                                {{ $head->amount ?? '00' }}
                                                {{ $head->amount_type == 'fixed' ? 'Tk - Fixed' : ($head->amount_type == 'percentage' ? '%' : 'x') }}
                                                <input type="hidden" class="amount_type" value="{{ $head->amount_type }}">
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
                                    <a href="{{ route('deductions.index') }}">here</a>
                                </i>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="row">
                            <div class="d-inline-block col-md-3" style="width: 200px">
                                <label for="">
                                    <b>Gross Salary:</b>
                                </label>
                                <input type="hidden" class="form-control salary" value="{{ $card->salary }}">
                                <input type="hidden" class="form-control basic_salary"
                                    value="{{ $card->basic_salary }}">
                                <span class="form-control">{{ $card->gross_salary }} Tk</span>
                            </div>
                            <div class="col-md-3" style="width: 200px">
                                <label for="">
                                    <b>Net Salary:</b>
                                </label>
                                <span class="form-control total">{{ $card->net_salary }} Tk</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        @if (role_can('payroll_card') ||
                        role_can('payroll_card.create') ||
                        role_can('payroll_card.create.self.super_department') || is_admin_of_super_department($card->employee?->department?->super_department_id))
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

</script>
@endSection
