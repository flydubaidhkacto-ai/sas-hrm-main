@extends('layouts.master')
@section('title', 'Roster')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Roster: Create
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('roster.index') }}">
                                    Roster
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Create </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div id="roster-create"
                 employees='@json($employees ?? [])'
                 employees_grouped='@json($employees_grouped ?? [])'
                 dates='@json($dates ?? [])'
                 super_departments='@json($super_departments ?? [])'
                 super_department_id='@json($super_department_id ?? null)'>
            </div>

            </div>
        </div>
    </div>
@endsection
