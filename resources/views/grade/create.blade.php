@extends('layouts.master')
@section('title', "Create Salary Grade")
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
                        <li class="breadcrumb-item"><a href="{{route('grades.index')}}">Salary Grade</a></li>
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
            <form class="col-md-12" action="{{ route('grades.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <div class="">
                        <label for="">Name:</label>
                        <input name="name" type="text" class="form-control" value="{{old('name')}}" required>
                    </div>
                </div>
                <table class="table ">
                    <thead>
                        <tr>
                            <th>
                                Code
                            </th>
                            <th>
                                Name
                            </th>
                            <th>
                                Category
                            </th>
                            <th style="width: 200px">
                                Amount
                            </th>
                            <th style="width: 160px">
                                Amount Type
                            </th>
                            <th style="width: 160px">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="heads">
                        @foreach ($heads as $i => $head)
                            <tr class="head">
                                <td>
                                    {{ $head->code }}
                                    <input name="heads[{{ $i }}][code]" type="hidden"
                                        class="form-control code" value="{{ $head->code }}">
                                    <br>
                                    <i class="preview"></i>
                                </td>
                                <td>
                                    {{ $head->name }}
                                    <input name="heads[{{ $i }}][name]" type="hidden"
                                        class="form-control name" value="{{ $head->name }}">
                                </td>
                                <td>
                                    {{ \App\Models\EarnHead::$categories[$head->category] ?? '' }}
                                    <input type="hidden" name="heads[{{ $i }}][category]"
                                        value="{{ $head->category }}">
                                </td>
                                <td>
                                    @if ($head->category === 'rewards' || $head->category ===
                                    'emp_daily_expenses')
                                    <i>Will be taken from system</i>
                                    <input name="heads[{{ $i }}][amount]" type="hidden"
                                        value="0">
                                    @else
                                    <input name="heads[{{ $i }}][amount]" type="number"
                                        step="any" class="form-control amount" required
                                        value="{{ $head->amount ?? '1' }}" min="0">
                                    @endif
                                </td>
                                <td>
                                    @if ($head->category === 'rewards' || $head->category ===
                                    'emp_daily_expenses')
                                    --
                                    <input name="heads[{{ $i }}][amount_type]" type="hidden"
                                        value="fixed">
                                    @else
                                    <select name="heads[{{ $i }}][amount_type]" @if($head->category == 'basic') disabled @endif
                                        class="form-control select amount_type" required>
                                        <option @if ($head->amount_type == 'fixed' || $head->category == 'basic') selected
                                            @endif
                                            value="fixed">Fixed Taka</option>
                                        <option @if ($head->amount_type == 'percentage')
                                            selected @endif
                                            value="percentage">Percentage</option>
                                        <option @if ($head->amount_type == 'other' ||
                                            $head->amount_type == '') selected @endif
                                            value="other">Multiply</option>
                                    </select>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-row-heads" {{
                                        $head->category == 'basic' ? 'disabled' : '' }}
                                        @if ($head->category == 'basic')
                                        data-toggle="tooltip"
                                        data-placement="top"
                                        title="Can't delete Basic"
                                        @endif
                                        >
                                        <i class="fa fa-trash m-r-5"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div>
                    <b>Total Amount: </b>
                    <span class="total"></span>
                    <input type="hidden" name="amount" class="total">
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
    $(document).ready(function () {
        $(".delete-row-heads").on("click", function (event) {
            event.preventDefault();
            if (confirm("Are you sure you want to delete this Earn Heads?")) {
                $(this).closest("tr").remove();
            }
        });
        function calc() {
            let total = 0;
            let basic = 0;
            $('table tr').each(function () {
                let amount = Number($(this).find('input[name="heads[' + $(this).index() + '][amount]"]').val())
                let category = $(this).find('input[name="heads[' + $(this).index() + '][category]"]').val()
                let amount_type = $(this).find('select[name="heads[' + $(this).index() + '][amount_type]"]').val()
                if (category == 'basic') {
                    basic = (amount)
                    total = basic + 0
                    return
                }
                if(
                    (['rewards', 'emp_daily_expenses', 'loan', 'fine', 'employee_bill', 'overtime', 'duty_bonuses']).includes(category)
                ) {
                    return
                }

                if (amount_type == 'fixed') {
                    total += amount
                } else if (amount_type == 'percentage') {
                    total += ((amount) / 100) * (basic)
                } else if (amount_type == 'other') {
                    total += ((basic) * (amount))
                }
            })
            $('span.total').html((total).toFixed(2) + ' Tk.')
            $('input[name="amount"]').val((total).toFixed(2))
        }
        $('table input, table select').on('change', function () {
            calc()
        })
        $('table input, table select').on('keyup', function () {
            calc()
        })
        $('table input, table select').on('blur', function () {
            calc()
        })
        calc()
    });
</script>
@endsection
@endsection
