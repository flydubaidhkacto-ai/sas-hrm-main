<div class="mt-2">
    <div class="row">
        <div class="col-md-6">
            <ul class="stepper">

                @php
                    $steps = $model->getApprovalSteps() ?: [];
                    $isSuperAdmin = user()->is_super_admin();
                    $aprovers = [];

                    // NEW: detect first rejected step
                    $firstRejectedOrder = null;
                    foreach($steps as $ord => $st){
                        if(strtolower($st['status'] ?? '') === 'rejected'){
                            $firstRejectedOrder = $ord;
                            break;
                        }
                    }
                @endphp

                @foreach ($steps as $order => $step)

                    @php
                        $aprovers[] = $step['approver'];

                        $status = strtolower($step['status'] ?? 'pending');
                        $isApproved = $status === 'approved';
                        $isRejected = $status === 'rejected';
                        $isPending  = $status === 'pending';

                        $isMyStep = $step['approver']->id == user()->id;

                        $showForm = false;
                        $showApproveBtn = false;
                        $showRejectBtn = false;

                        // =====================================
                        // 🔁 CHANGED: NORMAL APPROVER LOGIC
                        // =====================================
                        if(!$isSuperAdmin){
                            if(
                                $isMyStep
                                && $isPending
                                && (
                                    is_null($firstRejectedOrder)
                                    || $order < $firstRejectedOrder
                                )
                            ){
                                $showForm = true;
                                $showApproveBtn = true;
                                $showRejectBtn = true;
                            }
                        }

                        // =====================================
                        // 🔁 CHANGED: SUPER ADMIN OVERRIDE
                        // =====================================
                       if($isSuperAdmin && $isMyStep){
                            if(!$isApproved){
                                $showForm = true;
                                $showApproveBtn = true;

                                // If no one else rejects → reject button show
                                if(is_null($firstRejectedOrder)){
                                    $showRejectBtn = true;
                                }
                                // If someone else rejects it → just approve.
                                else{
                                    $showRejectBtn = false;
                                }
                            }
                        }
                    @endphp


                    <li class="step
                        @if ($isApproved) completed
                        @elseif($isRejected) rejected
                        @else pending @endif">

                        <div class="step-icon">
                            @if ($isApproved)
                                <i class="fas fa-check"></i>
                            @elseif($isRejected)
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

                                {{-- ACTION FORM --}}
                                @if ($showForm)
                                    <div class="mt-2">
                                        <form action="{{ route('approvals.approve', [
                                                'module' => $model_type,
                                                'module_id' => $model->id
                                            ]) }}" method="post">
                                            @csrf

                                            <input type="hidden"
                                                   name="approval_step_id"
                                                   value="{{ $step['step']->id }}">

                                            <input type="hidden"
                                                   name="approval_process_type"
                                                   value="{{ $model_type }}">

                                            <div class="form-group">
                                                <label>Remarks: (optional)</label>
                                                <textarea name="note"
                                                          class="form-control"
                                                          rows="1"></textarea>
                                            </div>

                                            @if($showApproveBtn)
                                                <button name="approve"
                                                        value="approve"
                                                        class="btn btn-sm btn-primary">
                                                    <i class="fa fa-check"></i>
                                                    Approve
                                                </button>
                                            @endif

                                            @if($showRejectBtn && !$step['step']?->is_publisher)
                                                <button name="reject"
                                                        value="reject"
                                                        class="btn btn-sm btn-danger">
                                                    <i class="fa fa-times"></i>
                                                    Reject
                                                </button>
                                            @endif
                                        </form>
                                    </div>

                                    {{-- HISTORY VIEW --}}
                                @else
                                    <div>
                                        {{ ucfirst($status) }}

                                        {{-- NEW --}}
                                        @if(!is_null($firstRejectedOrder) && $order > $firstRejectedOrder && $isPending)

                                        @endif

                                        @if($step['decided'])
                                            <i>
                                                at
                                                {{ $step['decided']->created_at->format('d M, Y h:i A') }}
                                                ({{ $step['decided']->created_at->diffForHumans() }})
                                            </i>
                                        @endif
                                    </div>
                                @endif

                                {{-- REMARKS --}}
                                @if($step['decided'] && $step['decided']->note)
                                    <div class="mt-1">
                                        <b>Remarks:</b>
                                        <div>
                                            {!! str_replace("\n", "<br>", $step['decided']->note) !!}
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </li>

                @endforeach

            </ul>
        </div>

        <div class="col-md-6">
            @include('data-query-request.query', [
                'model_type' => $model_type,
                'model_id' => $model->id,
                'applicant_id' =>$employee->id,
                'users' => array_merge([$employee], $aprovers)
            ])
        </div>
    </div>
</div>