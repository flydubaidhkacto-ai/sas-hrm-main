
{{-- create a modal for activate employee --}}
<div class="modal fade" id="activate-modal" tabindex="-1" role="dialog" aria-labelledby="activate-modal-label"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activate-modal-label">Activate Employee</h5>
            </div>
            <div class="modal-body">
                <div>
                    <b>Name: </b>
                    <span id="activate-modal-name"></span>
                    <br>
                    <b>Designation: </b>
                    <span id="activate-modal-designation"></span>
                    <br>
                    <b>Department: </b>
                    <span id="activate-modal-department"></span>
                    <br>
                    <b>Super Department: </b>
                    <span id="activate-modal-super-department"></span>
                </div>
                <form action="{{ route('employees.active.terminate') }}" method="post" id="activate-form" class="mt-3">
                    @csrf
                    <input type="hidden" name="employee_id" id="activate-modal-employee-id">
                    <div class="mt-2">
                        <label for="schedule_re_activation" class="d-block w-100">
                            Schedule Activation: &nbsp;
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
                    <div class="mt-2 d-none">
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
                    <button type="submit" class="btn btn-success mt-3">Activate</button>
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
    function openActivateModal(employeeId, name, designation, department, super_department) {
        $('#activate-modal').modal('show');
        $('#activate-modal-employee-id').val(employeeId);
        $('#activate-modal-name').text(name);
        $('#activate-modal-designation').text(designation);
        $('#activate-modal-department').text(department);
        $('#activate-modal-super-department').text(super_department);
    }
</script>
