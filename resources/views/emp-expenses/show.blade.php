<div id="printExp" style="width: 100%;">
    <div class="mb-2">
        @if(config('app.customer') == 'sas')
            <a class="btn btn-sm btn-info float-right" href="#"
                onclick='
                    let openWindow = window.open("Print", "title", "attributes");
                    openWindow.document.write(
                        "<html><head>" + document.querySelector("head").innerHTML + "</head><body><style>button,a {display: none !important;}</style> " +
                        `<h1 style="text-align:center"><span style="font-family:Comic Sans MS,cursive"><img alt="" src="/images/logo/flydubai.jpg" style="height:100px; margin:0px; width:300px" />&nbsp;</span></h1><p style="text-align:center"><span style="font-size:14px"><span style="font-family:Comic Sans MS,cursive"><strong>Bashati Avenue, Plot-10, Unit C-4, Road#53,Gulshan Avenue,</strong></span></span></p><p style="text-align:center"><span style="font-size:14px"><span style="font-family:Comic Sans MS,cursive"><strong>Gulshan-2, Dhaka-1212,Bangladesh</strong></span></span></p>` +
                        document.querySelector("#printExp").innerHTML +
                        "</body></html>"
                    );
                    openWindow.document.close();
                    openWindow.focus();
                    setTimeout(() => {
                        openWindow.print();
                        openWindow.close();
                    }, 400);
                '>
                <i class="fa fa-print"></i>
                Print
            </a>
        @endif
        <div class="row">
            <div class="col-3">
                <img src="/uploaded_files/{{ $expense->employee?->picture_file }}" alt="" class="w-100">
            </div>
            <div class="col-9">
                <h4>{{ $expense->employee?->name }}</h4>
                <p class="mb-0">
                    {{ $expense->employee?->department?->super_department?->name }}:
                    {{ $expense->employee?->department?->name }}
                </p>
                <p class="mb-0">{{ $expense->employee?->designation?->name }}</p>
                <p class="text-muted mb-0">{{ $expense->employee?->email }}</p>
                <p class="text-muted mb-0">{{ $expense->employee?->phone }}</p>
                <p class="text-muted mb-0">{{ $expense->employee?->address }}</p>
            </div>
        </div>
    </div>
    <div class="mb-2">
        <b>Amount:</b>
        {{ $expense->amount }} Tk
    </div>
    <div class="mb-2">
        <b>Date:</b>
        {{ carbon($expense->date)->format('d M, Y') }}
        <br>
        <b>Reason:</b>
        <h3>
            {{ $expense->name }}
        </h3>
        @if ($expense->description)
            {!! str_replace("\n", '<br>', $expense->description) !!}
        @endif
    </div>
    <div class="mb-2 d-none">
        <i>Description:</i>
        <div>{!! str_replace("\n", '<br>', $expense->description) !!}</div>
    </div>

    @include('layouts.approval', [
        'model' => $expense,
        'model_type' => 'emp_daily_expense',
        'employee' => $expense->employee,
        'have_form' => true,
    ])
</div>
