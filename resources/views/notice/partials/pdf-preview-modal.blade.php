<div class="modal fade" id="pdfPreviewModal" tabindex="-1" role="dialog" aria-labelledby="pdfPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfPreviewModalLabel">PDF Preview</h5>
                <button type="button" class="close pdf-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="pdfPreviewFrame" src="" frameborder="0" style="width:100%;height:100%;"></iframe>
            </div>
            <div class="modal-footer">
                <a id="pdfPreviewDownload" href="#" target="_blank" class="btn btn-primary btn-sm">
                    <i class="fa fa-download"></i> Open in New Tab
                </a>
                <button type="button" class="btn btn-secondary btn-sm pdf-modal-close" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    (function() {
        var currentBlobUrl = null;

        function setPdfUrl(url) {
            if (currentBlobUrl) {
                URL.revokeObjectURL(currentBlobUrl);
                currentBlobUrl = null;
            }
            if (url && url.indexOf('blob:') === 0) {
                currentBlobUrl = url;
            }
            $('#pdfPreviewFrame').attr('src', url);
        }

        function closePdfModal() {
            $('#pdfPreviewModal').modal('hide');
        }

        $(document).on('click', '.pdf-preview-btn', function() {
            var pdfUrl = $(this).data('url');
            var fileName = $(this).data('name') || 'PDF Preview';
            setPdfUrl(pdfUrl);
            $('#pdfPreviewDownload').attr('href', pdfUrl);
            $('#pdfPreviewModalLabel').text(fileName);
            $('#pdfPreviewModal').modal('show');
        });

        $(document).on('click', '.pdf-modal-close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closePdfModal();
        });

        $('#pdfPreviewModal').on('hidden.bs.modal', function() {
            setPdfUrl('');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });
    })();
</script>
