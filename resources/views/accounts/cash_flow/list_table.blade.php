<table class="table table-striped custom-table">
    <thead>
        <tr>
            <th>Category</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Operating Cash Flow</td>
            <td>{{ number_format($operatingCashFlow, 2) }}</td>
        </tr>
        <tr>
            <td>Investing Cash Flow</td>
            <td>{{ number_format($investingCashFlow, 2) }}</td>
        </tr>
        <tr>
            <td>Financing Cash Flow</td>
            <td>{{ number_format($financingCashFlow, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total Cash In</strong></td>
            <td><strong>{{ number_format($cashIn, 2) }}</strong></td>
        </tr>
        <tr>
            <td><strong>Total Cash Out</strong></td>
            <td><strong>{{ number_format($cashOut, 2) }}</strong></td>
        </tr>
        <tr>
            <td><strong>Net Cash Flow</strong></td>
            <td>
                <strong>
                    @if($netCashFlow >= 0)
                        ✅ {{ number_format($netCashFlow, 2) }} (Positive)
                    @else
                        ❌ {{ number_format($netCashFlow, 2) }} (Negative)
                    @endif
                </strong>
            </td>
        </tr>
    </tbody>
</table>
