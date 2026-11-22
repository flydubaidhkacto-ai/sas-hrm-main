@extends('layouts.master')
@section('title', "Generate Bonuses")
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Generate Bonuses</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('bonuses.index') }}">Bonuses</a></li>
                            <li class="breadcrumb-item active">Generate</li>
                        </ul>
                    </div>
                </div>
            </div>
            <form class="mb-3 generate">
                <div class="row filter-row">
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Super Department</label>
                        <select name="super_department" id="" class="form-control super-department-select2">
                            @if(request()->super_department)
                                <option value="{{ request()->super_department }}" selected>
                                    {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Department</label>
                        <select name="department" id="" class="form-control department-select2">
                            @if(request()->department)
                                <option value="{{ request()->department }}" selected>
                                    {{ \App\Models\Department::find(request()->department)?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Designation</label>
                        <select name="designation" id="" class="form-control designation-select2">
                            @if(request()->designation)
                                <option value="{{ request()->designation }}" selected>
                                    {{ \App\Models\Designation::find(request()->designation)?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Employee</label>
                        <select name="employee" id="" class="form-control employee-select2">
                            @if(request()->employee)
                                <option value="{{ request()->employee }}" selected>
                                    {{ \App\Models\User::find(request()->employee)?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Month</label>
                        <input name="month" type="text" class="form-control month-year-picker" value="{{ now()->format('m-Y') }}" required>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Amount: </label>
                        <input name="working_days" type="number" class="amount form-control" value="25" required>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Amount Type:</label>
                        <select name="" id="" class="form-control amount_type">
                            <option value="fixed">Flat (Tk)</option>
                            <option value="percentage">Percentage (%)</option>
                            {{-- <option value="multiply">Multiply (x)</option> --}}
                        </select>
                    </div>
                    <div class="mt-2 col-sm-6 col-md-3">
                        <label for="">Category</label>
                        <select name="" id="" class="form-control category">
                            @foreach ($categories as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-2 col-12">
                        <label for="all">
                            <input type="radio" name="all" id="all" value="all" checked>
                            <span>
                                Select All Employees
                            </span>
                        </label>
                        <br>
                        <input type="radio" name="all" id="some" value="some">
                        <label for="some">
                            <span>
                                Select Employees who have worked more than
                                <input type="number" name="some_months" style="width: 50px"> months and
                                <input type="number" name="some_years" style="width: 50px"> years
                            </span>
                        </label>
                    </div>
                    <div class="mt-3 col-12">
                        <button type="submit" class="btn btn-success">
                            Generate
                        </button>
                    </div>
                </div>
            </form>
            <div class="mt-2 generator-status w-100 d-none">
                <h5>Status</h5>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 1%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">1%</div>
                </div>
                <div class="mt-2 text-warning">
                    <i>
                        Please wait while we generate your bonuses
                    </i>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Wrapper -->
@endsection
@section('script')
    <script>
        $('.generate').on('submit', function(e){
            e.preventDefault()
            let form = $(this)
            let url = "{{ route("bonuses.generate") }}"
            let super_department = form.find('.super-department-select2').val()
            let department = form.find('.department-select2').val()
            let designation = form.find('.designation-select2').val()
            let employee = form.find('.employee-select2').val()
            let month = form.find('.month-year-picker').val()
            let amount = form.find('.amount').val()
            let amount_type = form.find('.amount_type').val()
            let category = form.find('.category').val()
            let condition = $('[name=all]:checked').val() == 'some'
            let data = {
                super_department,
                department,
                designation,
                employee,
                month,
                amount,
                amount_type,
                category,
            }
            if(condition)
            {
                let months = form.find('[name=some_months]').val() || 0
                let years = form.find('[name=some_years]').val() || 0
                if(months == 0 && years == 0)
                {
                    return alert('Please select some months or years or both')
                }
                data.condition = {
                    months, years
                }
            }
            form.addClass('d-none')
            $('.generator-status').removeClass('d-none')
            $('.generator-status .progress-bar').css('width', '1%')
            $('.generator-status .progress-bar').text('1%')
            $.ajax({
                url,
                type: 'POST',
                data,
                success: async function(response) {
                    // return
                    if(response.total_user_pages)
                    {
                        for (let index = 1; index <= response.total_user_pages; index++)
                        {
                            let data2 = {
                                ...data,
                                page: index,
                                step_2: 1,
                            }
                            await $.ajax({
                                url,
                                type: 'POST',
                                data: data2,
                                success: function(res) {
                                    if(res)
                                    {
                                        $('.generator-status .progress-bar').css('width', `${(index / response.total_user_pages) * 100}%`)
                                        $('.generator-status .progress-bar').text(`${Math.round((index / response.total_user_pages) * 100)}%`)
                                        setTimeout(() => {
                                            location = "{{ route("bonuses.index") }}?" + form.serialize();
                                        }, 2000);
                                    }
                                } ,
                                error: function(error) {
                                    $('.generator-status').html(`
                                        <div class="alert alert-danger">
                                            Failed to generate: ${error.responseJSON.message}
                                        </div>
                                    `)
                                }
                            });
                        }
                    }
                    else {
                        setTimeout(() => {
                            location = "{{ route("bonuses.index") }}?" + form.serialize();
                        }, 1000);
                    }
                },
                error: function(error) {
                    $('.generator-status').html(`
                        <div class="alert alert-danger">
                            Failed to generate: ${error.responseJSON.message}
                        </div>
                    `)
                }
            })
        })
    </script>
@endsection
