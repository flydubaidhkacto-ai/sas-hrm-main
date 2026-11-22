@php
    $box = Str::random(10) . $model_id;

    use App\Services\Approval\CommonService;
    $employees = CommonService::employeeOptions($model_type, $applicant_id);
@endphp

<div id="{{ $box }}"
     data-model="{{ $model_type }}"
     data-id="{{ $model_id }}">

    <div id="query-container-{{ $box }}"></div>

    @if(role_can('data.query.request'))

        <div class="mt-3">

            <div class="add-data-query-request-container d-none">

                <label><b>Employee</b></label>

                <select class="data-query-request-employee form-control select2"
                        multiple>
                    @foreach ($employees as $user)
                        <option value="{{ $user['id'] }}">
                            {{ $user['label'] }}
                        </option>
                    @endforeach
                </select>

                <input type="text"
                       class="query form-control mt-2"
                       placeholder="Enter query">

                <button type="button"
                        class="btn btn-primary btn-sm mt-2"
                        onclick="saveQuery('{{ $box }}')">
                    Send Query
                </button>

                <button type="button"
                        class="btn btn-secondary btn-sm mt-2"
                        onclick="cancelQuery('{{ $box }}')">
                    Cancel
                </button>

            </div>

            <button type="button"
                    class="btn btn-primary btn-sm add-data-query-request"
                    onclick="newQuery('{{ $box }}')">
                New Query
            </button>

        </div>

    @endif

</div>

<script>

    // Use a page-level flag to avoid duplicate `let` declarations when this
    // partial is included multiple times on the same page.
    window.dataQueryTyping = window.dataQueryTyping || false;
    function __set_data_query_typing(v) { window.dataQueryTyping = v; }
    var typing = window.dataQueryTyping;

    /* ---------------- INIT ---------------- */

    function initModule(box){
        loadQueries(box);

        setInterval(function(){
            if(!typing){
                loadQueries(box);
            }
        }, 5000);
    }

    /* ---------------- LOAD ---------------- */

    function loadQueries(box){

        if(typing) return;

        let container = $('#' + box);

        $.get(route('data.query.request.index'), {
            ajax: 1,
            query_for_model: container.data('model'),
            query_for_model_id: container.data('id'),
            box: box
        }, function(html){

            $('#query-container-' + box).html(html);

        });
    }

    /* ---------------- READY ---------------- */

    $(document).ready(function(){

        let box = $('[id][data-model]').attr('id');

        if(box){
            initModule(box);
        }

    });

    /* ---------------- TYPING FIX ---------------- */

    $(document).on('focus keydown', '.answer-box', function(){
        __set_data_query_typing(true);
        typing = true;
    });

    $(document).on('blur', '.answer-box', function(){
        setTimeout(() => { __set_data_query_typing(false); typing = false; }, 500);
    });

    /* ---------------- NEW QUERY ---------------- */

    function newQuery(box){

        let container = $('#' + box);
        let select = container.find('.data-query-request-employee');

        container.find('.add-data-query-request').addClass('d-none');
        container.find('.add-data-query-request-container').removeClass('d-none');

        if(select.hasClass("select2-hidden-accessible")){
            select.select2('destroy');
        }

        select.val(null);

        select.select2({
            width: '100%',
            dropdownParent: container
        });

        container.find('.query').val('');
    }

    /* ---------------- CANCEL ---------------- */

    function cancelQuery(box){

        let container = $('#' + box);
        let select = container.find('.data-query-request-employee');

        container.find('.add-data-query-request').removeClass('d-none');
        container.find('.add-data-query-request-container').addClass('d-none');

        if(select.hasClass("select2-hidden-accessible")){
            select.select2('destroy');
        }

        select.val(null);

        select.select2({
            width: '100%',
            dropdownParent: container
        });

        container.find('.query').val('');
    }

    /* ---------------- SAVE QUERY ---------------- */

    function saveQuery(box){

        let container = $('#' + box);

        let select = container.find('.data-query-request-employee');
        let queryInput = container.find('.query');

        let emp = select.val();
        let query = queryInput.val();

        if(!emp || !query){
            alert('Fill all fields');
            return;
        }

        $.post(route('data.query.request.store'), {
            employee_ids: emp,
            query: query,
            query_for_model: container.data('model'),
            query_for_model_id: container.data('id'),
        }, function(){

            loadQueries(box);

            if(select.hasClass("select2-hidden-accessible")){
                select.select2('destroy');
            }

            select.val(null);

            select.select2({
                width: '100%',
                dropdownParent: container
            });

            queryInput.val('');

            cancelQuery(box);

        });

    }

    /* ---------------- SAVE ANSWER ---------------- */

    function saveAnswer(btn){

        event.preventDefault();

        let card = $(btn).closest('.card');
        let textarea = card.find('.answer-box');

        let answer = textarea.val();
        let id = textarea.data('id');

        if(!answer){
            alert('Answer required');
            return;
        }

        let mainBox = $(btn).closest('[id][data-model]');
        let boxId = mainBox.attr('id');

        $.post(route('data.query.request.answer.store'), {
            data_query_request_id: id,
            answer: answer,
        }, function(){

            typing = false;

            loadQueries(boxId);

        });

    }

</script>