<table class="table table-striped custom-table">
    <thead>
        <tr>
            <th>Total Assets</th>
            <th>Total Liabilities</th>
            <th>Total Equity</th>
            <th>Total Revenue</th>
            <th>Total Expense</th>
            <th>Net Income</th>
            <th>Assets = Liabilities + Equity</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ number_format($totalAssets, 2) }}</td>
            <td>{{ number_format($totalLiabilities, 2) }}</td>
            <td>{{ number_format($totalEquity, 2) }}</td>
            <td>{{ number_format($totalRevenue, 2) }}</td>
            <td>{{ number_format($totalExpense, 2) }}</td>
            <td>{{ number_format($netIncome, 2) }}</td>
            <td>
                @if($totalAssets == ($totalLiabilities + $totalEquity))
                    ✅ Balanced
                @else
                    ❌ Not Balanced
                @endif
            </td>
        </tr>
    </tbody>
</table>
