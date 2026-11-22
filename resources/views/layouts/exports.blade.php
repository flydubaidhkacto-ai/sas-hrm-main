
<div style="text-align: end" class="mb-2">
    @if($no_docx ?? false)
    @else
        <a href="{{ add_query_to_current_route(['export' => 'docx']) }}"
            class="btn btn-sm btn-info" target="_blank">
            <i class="fa fa-file-word-o"></i>
            Docx
        </a>
    @endif
    <a href="{{ add_query_to_current_route(['export' => 'xlsx']) }}"
        class="btn btn-sm btn-info" target="_blank">
        <i class="fa fa-file-excel-o"></i>
        Excel
    </a>
    @if($no_pdf ?? false)
    @else
    <a href="{{ add_query_to_current_route(['export' => 'pdf']) }}"
        class="btn btn-sm btn-info" target="_blank">
        <i class="fa fa-file-pdf-o"></i>
        PDF
    </a>
    @endif
    @if($no_print ?? false)
    @else
    <a href="{{ add_query_to_current_route(['export' => 'print']) }}"
        class="btn btn-sm btn-info" target="_blank">
        <i class="fa fa-print"></i>
        Print
    </a>
    @endif
</div>
