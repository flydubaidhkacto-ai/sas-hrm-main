<table class="table table-striped custom-table">
    <thead>
        <tr>
            <th>Total Revenue (Income)</th>
            <th>Total Expenses</th>
            <th>Profit/Loss</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>${{ number_format($totalRevenue, 2) }}</td>
            <td>${{ number_format($totalExpenses, 2) }}</td>
            <td>
                @if($profitOrLoss >= 0)
                    <span class="text-success">${{ number_format($profitOrLoss, 2) }}</span>
                @else
                    <span class="text-danger">-${{ number_format(abs($profitOrLoss), 2) }}</span>
                @endif
            </td>
        </tr>
    </tbody>
</table>
