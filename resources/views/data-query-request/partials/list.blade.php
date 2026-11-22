@if($groupedQueries->count())

    <div class="data-queryies">

        @foreach ($groupedQueries as $queries)

            @php $first = $queries->first(); @endphp

            <div class="card mb-3">

                {{-- HEADER --}}
                <div class="card-header">

                    <b>
                        {{ $first->requestedBy?->name }}

                        {{-- ✅ Only sender sees employee count --}}
                        @if($first->requested_by == user()->id)
                            → {{ $queries->count() }} Employees
                        @endif
                    </b>

                    <br>

                    <span>{{ $first->query }}</span>

                    <span class="float-right">
                        <small class="text-muted">
                            {{ $first->created_at->format('d M, Y h:i A') }}
                        </small>
                    </span>

                </div>

                {{-- BODY --}}
                <div class="card-body">

                    @foreach ($queries as $data_query_request)

                        <div class="border rounded p-2 mb-2">

                            <b>
                                <i class="fa fa-user"></i>
                                {{ $data_query_request->requestedTo?->name }}
                            </b>

                            {{-- ANSWERED --}}
                            @if($data_query_request->query_response_description)

                                <span class="badge badge-success ml-2">Answered</span>

                                <div style="white-space: pre-wrap;">
                                    {!! $data_query_request->query_response_description !!}
                                </div>

                                <small class="text-muted float-right">
                                    {{ $data_query_request->updated_at->format('d M, Y h:i A') }}
                                </small>

                                {{-- CAN ANSWER --}}
                            @elseif($data_query_request->requested_to == user()->id)

                                <span class="badge badge-warning ml-2">Pending (You)</span>

                                <div class="form-group mt-2">

                                    <textarea class="form-control answer-box"
                                              data-id="{{ $data_query_request->id }}"
                                              rows="2"
                                              placeholder="Write your answer..."></textarea>

                                </div>

                                <button type="button"
                                        class="btn btn-primary btn-sm"
                                        onclick="saveAnswer(this)">
                                    <i class="fa fa-check"></i>
                                    Send Answer
                                </button>

                                {{-- PENDING --}}
                            @else

                                <span class="badge badge-secondary ml-2">Pending</span>

                            @endif

                        </div>

                    @endforeach

                </div>

            </div>

        @endforeach

    </div>

@endif