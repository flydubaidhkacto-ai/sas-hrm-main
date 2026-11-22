@extends('layouts.master')
@section('title', 'Inventory Item Request Report')
@section('content')

<div class="page-wrapper">
  <div class="content container-fluid">
    <div class="page-header">
      <div class="row align-items-center">
        <div class="col">
          <h3 class="page-title">Inventory Item Request Report</h3>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Inventory Item Request Report</li>
          </ul>
        </div>
      </div>
    </div>

    {!! Toastr::message() !!}
    @include('layouts.alerts')

    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" action="{{ route('inventory_item_request.report') }}" class="row">
          <div class="col-md-2">
            <div class="form-group">
              <label>Date From</label>
              <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Date To</label>
              <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value="all" {{ (isset($filters['status']) && $filters['status']=='all')?'selected':'' }}>All</option>
                <option value="pending" {{ (isset($filters['status']) && $filters['status']=='pending')?'selected':'' }}>Pending</option>
                <option value="approved" {{ (isset($filters['status']) && $filters['status']=='approved')?'selected':'' }}>Approved</option>
                <option value="rejected" {{ (isset($filters['status']) && $filters['status']=='rejected')?'selected':'' }}>Rejected</option>
              </select>
            </div>
          </div>
          <div class="col-sm-6 col-md-3">
              <label for="">Employee</label>
              <select name="employee_id" id="" class="form-control employee-select2">
                  @if(request()->employee_id)
                  <option value="{{ request()->employee_id }}" selected>
                      {{ \App\Models\User::find(request()->employee)?->name }}
                  </option>
                  @endif
              </select>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Item</label>
              <select name="inventory_item_id" class="form-control">
                <option value="all">All</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}" {{ (isset($filters['inventory_item_id']) && $filters['inventory_item_id']==$item->id)?'selected':'' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('inventory_item_request.report') }}" class="btn btn-secondary">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <table class="table table-striped custom-table mb-0">
          <thead>
            <tr>
              <th>S.L</th>
              <th>Request Date</th>
              <th>Emp. ID</th>
              <th>Emp. Name</th>
              <th>Items</th>
              <th>Status</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($inventory_requests as $inv)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $inv->created_at->format('Y-m-d') }}</td>
              <td>{{ $inv->employee_id }}</td>
              <td>{{ $inv->employee->name }}</td>
              <td>
                @foreach($inv->inventoryItems as $inventoryItem)
                  <div>{{ $inventoryItem->code }} - {{ $inventoryItem->name }} ({{ $inventoryItem->pivot->quantity }})</div>
                @endforeach
              </td>
              <td>
                @if($inv->status=='pending')
                <span class="badge badge-warning">Pending</span>
                @elseif($inv->status=='approved')
                <span class="badge badge-info">Approved</span>
                @elseif($inv->status=='rejected')
                <span class="badge badge-danger">Rejected</span>
                @endif
              </td>
              <td>{{ $inv->description }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@section('script')
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
  new DataTable('table', {});
</script>
@endsection
@endsection
