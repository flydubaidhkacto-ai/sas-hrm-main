<div>
    <b>Reason: </b> {{ $fine->reason }} <br>
    <b>Amount: </b> {{ $fine->amount }} <br>
    <b>Pay Term: </b> {{ $fine->cut_off_as }} <br>
    <b>Date: </b> {{ $fine->created_at->format('d M, Y') }} <br>
</div>

@include('layouts.approval', [
    'model' => $fine,
    'model_type' => 'fine',
    'employee' => $fine->employee,
    'have_form' => true,
])
