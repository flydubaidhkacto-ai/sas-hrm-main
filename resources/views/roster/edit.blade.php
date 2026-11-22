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
                            Roster: Edit
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('roster.index') }}">
                                    Roster
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Edit </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="roster-edit"
                 employees='@json($employees ?? [])'
                 employees_grouped='@json($employees_grouped)'
                 roster='@json($roster)'
                 roster_data='@json($roster->formatted())'
                 copy_mode='@json(session("copy_mode", false))'
                super_departments='@json($super_departments ?? [])'>
            </div>
        </div>
    </div>
@endsection







