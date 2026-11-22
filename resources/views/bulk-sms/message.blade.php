@extends('layouts.master')
@section('title', "SMS Message")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        SMS
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Send SMS</li>
                    </ul>
                </div>
            </div>
        </div>
        {{-- action="{{ route('sms.message.send') }}" --}}
        <form class="message" method="POST">
            @csrf
            <div class="row">
                <div class="form-group col-sm-6 col-md-6">
                    <label for="exampleFormControlTextarea1">Type Your Message</label>
                    <textarea class="form-control" id="mssg" name="message" rows="6"></textarea>
                </div>
            </div>
            @foreach ($selectedId as $item)
            <input type="hidden" name="use[]" id="" value="{{ $item }}">
            @endforeach
            {!! Toastr::message() !!}
            <div class="mt-3">
                <button type="submit" id="msg" class="btn btn-primary">Update</button>
            </div>
        </form>
        <h5 class="status-message d-none">Status</h5>
        <div class="progress d-none" id="progress">
            <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0"
                aria-valuemin="0" aria-valuemax="100">1%</div> <br>
        </div>

        <div class="text-warning mt-2 status-message d-none">
            <i>
                Please wait while we sending your Message
            </i>
        </div>
    </div>
</div>
@json($selectedId)
@endsection

@section('script')
{{-- <script>
    var myArray = {!! json_encode($selectedId) !!};
         console.log(myArray); // This will log your Laravel array data in JavaScript
</script> --}}
<script>
    $('.message').on('submit', function(e) {
            e.preventDefault();
            var selectedIds = {!! json_encode($selectedId) !!}; /* Retrieve selected IDs */
            console.log(selectedIds);
            var totalMessages = selectedIds.length;
            var sentMessages = 0;
            $('.message').addClass('d-none');
            $('.status-message').removeClass('d-none');
            selectedIds.forEach(function(id) {
                // Send the message for the current ID
                // You may use AJAX to send the message and handle success/failure
                // Increment sentMessages when the message is successfully sent
                // Example AJAX request
                var message = $('#mssg').val();
                var data = {
                    id: id,
                    message: message
                }
                console.log(data);
                $.ajax({
                    url: '{{ route('sms.message.send') }}',
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        sentMessages++;
                        updateProgressBar(sentMessages, totalMessages);
                        if(sentMessages == totalMessages)
                        {
                            $('.status-message').addClass('text-success');
                            $('.status-message').html(`
                                <i>
                                    Message Sent Successfully
                                </i>
                            `)
                        }
                    }
                });
            });

        });

        function updateProgressBar(sentMessages, totalMessages) {
            // Calculate the percentage of completion
            var progress = (sentMessages / totalMessages) * 100;
            // Update the width of the progress bar

            $('#progress').removeClass('d-none');
            $('#progress-bar').css('width', progress + '%');
            $('#progress-bar').text(Math.round(progress) + '%');
            $('#progress-bar').attr('aria-valuenow', progress);
        }
</script>
@endsection