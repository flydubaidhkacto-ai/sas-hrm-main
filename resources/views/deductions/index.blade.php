
@extends('layouts.master')
@section('title', "Deductions")
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
                            Deductions
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                Deductions
                            </li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="{{ route('deductions.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add New</a>
                    </div>
                </div>
            </div>

            <!-- /Page Header -->
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <div class="row">
                <div class="col-md-12">
                    <div>
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 30px;">#</th>
                                    <th>
                                        Code
                                    </th>
                                    <th>Name</th>
                                    <th>
                                        Amount
                                    </th>
                                    <th>
                                        Category
                                    </th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $key => $item )
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>
                                        {{ $item->code }}
                                    </td>
                                    <td hidden class="id">{{ $item->id }}</td>
                                    <td class="earn_head">
                                        {{ $item->name }}
                                    </td>
                                    <td>
                                        @if($item->category == 'loan' || $item->category == 'fine')
                                            --
                                        @else
                                            {{ $item->amount }}
                                            {{
                                                $item->amount_type === 'fixed' ? "TK - Fixed" : ($item->amount_type === 'percentage' ? "%" : "x")
                                            }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ \App\Models\Deduction::$categories[$item->category] ?? '' }}
                                    </td>
                                    <td class="text-right">
                                        <div class="dropdown dropdown-action">
                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                <i class="material-icons">more_vert</i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item edit_earn_head" href="{{ route('deductions.edit', $item) }}">
                                                    <i class="fa fa-pencil m-r-5"></i> Edit
                                                </a>
                                                <a class="dropdown-item"
                                                    onclick="
                                                    if(confirm('Are you sure?'))
                                                    {
                                                        $(this).next().submit()
                                                    }
                                                    "
                                                >
                                                    <i class="fa fa-trash-o m-r-5"></i> Delete
                                                </a>
                                                <form method="POST" action="{{ route('deductions.delete', $item) }}" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
