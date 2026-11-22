<table class="table table-striped custom-table">
    <thead>
        <tr>
            <th>SL. No</th>
            <th>Category</th>
            <th>Budget Limit</th>
            <th>Amount Spent</th>
            <th>Remaining</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($budgets as $budget)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $budget->category }}</td>
            <td>{{ $budget->budget_limit }}</td>
            <td>{{ $budget->amount_spent }}</td>
            <td>{{ $budget->remainingAmount() }}</td>
            <td>
                <a href="{{ route('budgets.edit', $budget->id) }}" class="btn btn-warning"><i class="fa-solid fa-pen-to-square"></i></a>
                <form action="{{ route('budgets.destroy', $budget->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this budget?')"><i class="fa-solid fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
