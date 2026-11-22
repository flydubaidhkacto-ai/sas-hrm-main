
<!-- /Page Wrapper -->
<div class="modal fade" id="regenerateModal" tabindex="-1" aria-labelledby="regenerateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('regenerate.attendance.report') }}">
            <div class="modal-header">
                <h5 class="modal-title" id="regenerateModalLabel">Regenerate Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="">From</label>
                    <div class="cal-icon">
                        <input class="form-control datetimepicker" type="text" 
                            name="from_date" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="">To</label>
                    <div class="cal-icon">
                        <input class="form-control datetimepicker" type="text" 
                            name="to_date" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Regenerate</button>
            </div>
        </form>
    </div>
</div>