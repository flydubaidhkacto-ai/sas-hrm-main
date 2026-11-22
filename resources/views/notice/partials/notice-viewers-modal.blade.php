<div class="modal fade" id="noticeViewersModal" tabindex="-1" role="dialog" aria-labelledby="noticeViewersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 700px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noticeViewersModalLabel">Notice Viewers</h5>
                <button type="button" class="close viewers-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="noticeViewersLoading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading viewers...</p>
                </div>
                <div id="noticeViewersContent" style="display: none;">
                    <h6 id="noticeViewersTitle" class="mb-3"></h6>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Viewed At</th>
                                </tr>
                            </thead>
                            <tbody id="noticeViewersTableBody">
                            </tbody>
                        </table>
                    </div>
                    <p id="noticeViewersTotal" class="mt-2 text-muted"></p>
                </div>
                <div id="noticeViewersError" class="text-center py-4" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    <p class="mt-2" id="noticeViewersErrorMessage"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm viewers-modal-close" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    (function() {
        $(document).on('click', '.notice-viewers-btn', function() {
            var noticeId = $(this).data('notice-id');
            var noticeTitle = $(this).data('notice-title') || 'Notice Viewers';

            $('#noticeViewersLoading').show();
            $('#noticeViewersContent').hide();
            $('#noticeViewersError').hide();
            $('#noticeViewersTitle').text(noticeTitle);
            $('#noticeViewersModal').modal('show');

            $.ajax({
                url: '/notice/viewers/' + noticeId,
                type: 'GET',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $('#noticeViewersLoading').hide();
                    if (data && data.viewers && data.viewers.length > 0) {
                        var html = '';
                        data.viewers.forEach(function(viewer, index) {
                            html += '<tr>';
                            html += '<td>' + (index + 1) + '</td>';
                            html += '<td>' + (viewer.employee_id || 'N/A') + '</td>';
                            html += '<td>' + viewer.name + '</td>';
                            html += '<td>' + viewer.viewed_at + '</td>';
                            html += '</tr>';
                        });
                        $('#noticeViewersTableBody').html(html);
                        $('#noticeViewersTotal').text('Total: ' + data.total_viewers + ' employee(s) viewed this notice');
                        $('#noticeViewersContent').show();
                    } else {
                        $('#noticeViewersTableBody').html('<tr><td colspan="4" class="text-center">No viewers found</td></tr>');
                        $('#noticeViewersTotal').text('');
                        $('#noticeViewersContent').show();
                    }
                },
                error: function(xhr) {
                    $('#noticeViewersLoading').hide();
                    var msg = 'Failed to load viewers';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        msg = xhr.responseJSON.error;
                    }
                    $('#noticeViewersErrorMessage').text(msg);
                    $('#noticeViewersError').show();
                }
            });
        });

        $(document).on('click', '.viewers-modal-close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#noticeViewersModal').modal('hide');
        });

        $('#noticeViewersModal').on('hidden.bs.modal', function() {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });
    })();
</script>
