<div class="mt-2">
    <div class="row">
        <div class="col-md-6">
            <ul class="stepper">
                @php


                    $aprovers = [];
                @endphp
                @foreach ($model->getApprovalSteps() ?: [] as $order => $step)
                    @if(
                        ($step['approver']->id == user()->id && $step['step']->is_publisher)
                        || (!$step['step']->is_publisher)
                    )
                        @php
                            $aprovers[] = $step['approver'];
                        @endphp
                        <li
                                class="step @if ($step['status'] === 'approved') completed @elseif($step['status'] === 'rejected') rejected @else pending @endif">
                            <div class="step-icon">
                                @if ($step['status'] === 'approved')
                                    <i class="fas fa-check"></i>
                                @elseif($step['status'] === 'rejected')
                                    <i class="fas fa-times"></i>
                                @else
                                    <i class="fas fa-hourglass-start"></i>
                                @endif
                            </div>
                            <div class="step-content">
                                <div class="step-title">
                                    Step {{ $order }}: {{ $step['approver']?->name }}
                                </div>
                                <div class="step-description">
                                    {{-- can approve --}}
                                    @if ($step['can_approve'])
                                        <div class="mt-2">

                                            @if($have_form ?? null)
                                                <form
                                                        action="{{ route('approvals.approve', ['module' => $model_type, 'module_id' => $model->id]) }}"
                                                        method="post">

                                                    <div class="meta-data"></div>
                                                    @endif
                                                    <div>
                                                        @csrf
                                                        <input type="hidden" name="approval_step_id"
                                                               value="{{ $step['step']->id }}">
                                                        <input type="hidden" name="approval_process_type" value="{{ $model_type }}">
                                                        <div class="form-group">
                                                            <label for="">Remarks: (optional)</label>
                                                            <textarea name="note" class="form-control" rows="1"></textarea>
                                                        </div>
                                                        <button name="approve" value="approve" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-check"></i>
                                                            @if($step['step']?->is_publisher)
                                                                Publish
                                                            @else
                                                                Approve
                                                            @endif
                                                        </button>
                                                        @if(!$step['step']?->is_publisher)
                                                            <button name="reject" value="reject" class="btn btn-sm btn-danger">
                                                                <i class="fa fa-times"></i>
                                                                Reject
                                                            </button>
                                                        @endif
                                                    </div>
                                                    @if($have_form ?? null)
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <div>
                                            {{ ucfirst($step['status']) }}
                                            @if($step['decided'])
                                                <i>
                                                    at
                                                    {{ $step['decided']->created_at->format('d M, Y h:i A') }} ({{ $step['decided']->created_at->diffForHumans() }})
                                                </i>
                                            @endif
                                        </div>
                                    @endif

                                    @if($step['decided'] && $step['decided']->note)
                                        <div>
                                        </div>
                                        <div>
                                            <b>Remarks: </b>
                                            <div id="remarks" record-id="{{ $step['decided']->id }}">
                                                @if (role_can('edit-remarks') && (
                                                    ($step['approver']->id == user()->id)
                                                    || user()->is_super_admin()
                                                ))
                                                    <a class="btn btn-sm btn-primary" onclick="editRemarks(this)" href="#">
                                                        <i class="fa fa-pen"></i>
                                                    </a>
                                                @endif
                                                <div>
                                                    {!! str_replace("\n", "<br>", $step['decided']->note) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <script>
                                            window.editRemarks = (button) => {
                                                button.parentElement.parentElement.querySelector('#remarks')
                                                    .innerHTML = `
                                                    <textarea name="note" class="form-control" rows="2">${button.parentElement.parentElement.querySelector('#remarks div').innerText.trim()}</textarea>
                                                    <br/>
                                                    <a href="#" class="btn btn-sm btn-info"
                                                        onclick="saveRemarks(this)"><i class="fa fa-check"></i> Save</a>`;
                                            }
                                            window.saveRemarks = async (button) => {
                                                let val = button.parentElement.parentElement.querySelector('#remarks').querySelector('textarea').value.trim();
                                                let recordId = button.parentElement.parentElement.querySelector('#remarks').getAttribute('record-id');
                                                button.disabled = true;
                                                try {
                                                    let response = await axios.post("{{ route('approvals.update-remarks') }}", {
                                                        note: val,
                                                        record_id: recordId,
                                                    });
                                                    button.disabled = false;
                                                } catch (error) {
                                                    button.disabled = false;
                                                    return toastr.error('Something went wrong.' + error.response?.data?.message);
                                                }
                                                toastr.success('Remarks has been updated.');
                                                button.parentElement.parentElement.querySelector('#remarks')
                                                    .innerHTML = `
                                                    <a href="#" class="btn btn-sm btn-primary" onclick="editRemarks(this)"><i class="fa fa-pen"></i></a>
                                                    <div>${button.parentElement.parentElement.querySelector('#remarks').querySelector('textarea').value.trim().replace(/\n/g, "<br>")}</div>`;
                                            }
                                        </script>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
        <div class="col-md-6">
            @include('data-query-request.query', [
                'model_type' => $model_type,
                'model_id' => $model->id,
                'applicant_id' =>$employee->id,
                'users' => [$employee, ...$aprovers]
            ])
        </div>
    </div>
</div>
