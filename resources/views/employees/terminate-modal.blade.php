
{{-- create a modal for terminate employee --}}
<div class="modal fade" id="terminate-modal" tabindex="-1" role="dialog" aria-labelledby="terminate-modal-label"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="terminate-modal-label">Terminate Employee</h5>
            </div>
            <div class="modal-body">
                <div>
                    <b>Name: </b>
                    <span id="terminate-modal-name"></span>
                    <br>
                    <b>Designation: </b>
                    <span id="terminate-modal-designation"></span>
                    <br>
                    <b>Department: </b>
                    <span id="terminate-modal-department"></span>
                    <br>
                    <b>Super Department: </b>
                    <span id="terminate-modal-super-department"></span>
                </div>
                <form action="{{ route('employees.active.terminate') }}" method="post" id="terminate-form" class="mt-3">
                    @csrf
                    <input type="hidden" name="employee_id" id="terminate-modal-employee-id">
                    <div>
                        <label for="schedule_termination" class="d-block w-100">
                            Schedule Termination: &nbsp;
                            <span class="">
                                <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Schedule termination date and time"></i>
                            </span>
                            <a href="#" class="float-right" id="clear-schedule-termination">
                                <small>
                                    Clear Date-Time
                                </small>
                            </a>
                        </label>
                        <input type="datetime-local" name="schedule_termination" id="schedule_termination" class="form-control">
                    </div>
                    <div class="mt-2 d-none">
                        <label for="schedule_re_activation" class="d-block w-100">
                            Schedule Re-Activation: &nbsp;
                            <span class="">
                                <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Schedule re-activation date and time"></i>
                            </span>
                            <a href="#" class="float-right" id="clear-schedule-re-activation">
                                <small>
                                    Clear Date-Time
                                </small>
                            </a>
                        </label>
                        <input type="datetime-local" name="schedule_re_activation" id="schedule_re_activation" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-danger mt-3">Terminate</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#clear-schedule-termination').click(function(e) {
            e.preventDefault();
            $('#schedule_termination').val('');
        })
        $('#clear-schedule-re-activation').click(function(e) {
            e.preventDefault();
            $('#schedule_re_activation').val('');
        })
    })
    function openTerminateModal(employeeId, name, designation, department, super_department) {
        $('#terminate-modal').modal('show');
        $('#terminate-modal-employee-id').val(employeeId);
        $('#terminate-modal-name').text(name);
        $('#terminate-modal-designation').text(designation);
        $('#terminate-modal-department').text(department);
        $('#terminate-modal-super-department').text(super_department);
    }
</script>
