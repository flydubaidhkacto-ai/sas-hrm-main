@extends('layouts.master')
@section('title', 'Super Department')

@push('style')
    <style>
        .chat-container {
            display: flex;
            height: 80vh;
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
        }

        .chat-sidebar {
            width: 25%;
            background: #fff;
            border-right: 1px solid #ddd;
            padding: 10px;
            overflow-y: auto;
            height: 100%;
        }

        .search-bar {
            margin-bottom: 10px;
        }

        .chat-body {
            width: 75%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #007bff;
            padding: 15px;
        }

        .input-message {
            flex-grow: 1;
            border: none;
            padding: 10px;
            border-radius: 5px;
        }

        .send-btn {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            margin-left: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .file-upload {
            margin-top: 10px;
        }

        .messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            display: flex;
            flex-direction: column;
        }

        /* Message container */
        .message-container {
            display: flex;
            margin-bottom: 10px;
            max-width: 70%;
        }

        /* Sent messages (right side, blue bubble) */
        .sent-message,
        .received-message {
            align-self: flex-start;
            max-width: 70%;
            margin-bottom: 10px;
            padding: 2px 5px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
            position: relative;
        }

        .sent-message {
            background: #fff;
            /* Light green background */
            color: #333;
            align-self: flex-end;
        }

        .received-message {
            background: #f1f1f1;
            /* Light gray background for received messages */
            color: #333;
        }

        .message-content {
            display: flex;
            flex-direction: column;
            gap: 5px;
            /* Add small gap between text and time */
        }

        .message-text {
            margin: 0;
            word-wrap: break-word;
        }

        .message-meta {
            display: flex;
            justify-content: flex-end;
            /* Align the time to the right */
            font-size: 12px;
            color: #888;
        }

        .message-time {
            margin-left: 10px;
            /* Space between message text and time */
        }

        .message-file {
            font-size: 12px;
            color: #333;
        }

        .message-file a {
            text-decoration: none;
            color: inherit;
        }

        .message-file i {
            margin-right: 5px;
        }



        /* Received messages (left side, light gray bubble) */
        .received-message {
            align-self: flex-start;
            text-align: left;
            background: #f1f0f0;
            color: black;
            padding: 12px 15px;
            border-radius: 18px 18px 18px 0;
            max-width: 60%;
            font-size: 14px;
            line-height: 1.4;
            position: relative;
        }

        /* Message text spacing */
        .message-content {
            padding: 10px;
            display: inline-block;
            word-wrap: break-word;
        }

        /* Timestamp styling */
        .message-time {
            display: block;
            font-size: 11px;
            margin-top: 5px;
            opacity: 0.7;
            text-align: right;
        }


        /* Adjusting the layout of chat */
        .chat-body {
            display: flex;
            flex-direction: column;
            height: 100%;
            background: #f5f7fb;
        }

        /* Chat header styling */
        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 15px 25px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #ddd;
            font-family: 'Arial', sans-serif;
        }

        #chat-user-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .chat-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            /* Adds space between icons/buttons */
        }

        .video-call-icon,
        .call-icon {
            font-size: 24px;
            color: #007bff;
            cursor: pointer;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .video-call-icon:hover,
        .call-icon:hover {
            color: #0056b3;
            transform: scale(1.1);
            /* Slightly enlarges on hover */
        }

        /* Button styling */
        .block-btn,
        .unblock-btn {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 5px;
            transition: opacity 0.3s ease;
        }

        .block-btn {
            background-color: #dc3545;
            color: white;
        }

        .unblock-btn {
            background-color: #28a745;
            color: white;
        }

        .block-btn:hover,
        .unblock-btn:hover {
            opacity: 0.8;
        }

        /* Responsiveness */
        @media (max-width: 768px) {
            .chat-header {
                padding: 10px 15px;
            }

            .chat-actions {
                gap: 10px;
            }

            .video-call-icon,
            .call-icon {
                font-size: 20px;
            }
        }

        /* Chat input field styling */
        .message-input {
            display: flex;
            padding: 12px;
            background: #ffffff;
            border-top: 1px solid #ddd;
            align-items: center;
        }

        .input-message {
            flex-grow: 1;
            border: none;
            padding: 10px;
            font-size: 14px;
            border-radius: 25px;
            background: #f1f0f0;
            outline: none;
        }

        .send-btn {
            background: #0084ff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            margin-left: 10px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        .send-btn:hover {
            background: #006edc;
        }

        .user-item {
            cursor: pointer;
            /* Makes it clickable */
            padding: 10px;
            border-bottom: 1px solid #ddd;
            transition: background 0.3s ease-in-out;
        }

        .user-item:hover {
            background-color: #007bff;
            /* Changes background on hover */
            color: white;
            /* Changes text color on hover */
        }

        /* user image */
        .user-item {
            display: flex;
            align-items: center;
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
            transition: background 0.3s ease-in-out;
        }

        .user-image {
            width: 30px;
            /* Set the size of the image */
            height: 30px;
            /* Set the size of the image */
            border-radius: 50%;
            /* Make it circular */
            margin-right: 10px;
            /* Space between the image and username */
        }

        .user-name {
            flex-grow: 1;
            /* Make the name take up remaining space */
        }

        .latest-message {
            font-size: 12px;
            /* Smaller text for the latest message */
            color: #555;
            /* Slightly gray color for demo messages */
            max-width: 200px;
            /* Limit the width of the message */
            white-space: nowrap;
            /* Prevent message from wrapping */
            overflow: hidden;
            text-overflow: ellipsis;
            /* Add ellipsis for long messages */
            margin-left: 10px;
            /* Space between the name and latest message */
        }

        .user-item:hover {
            background-color: #007bff;
            color: white;
        }

        /* Attach the icon inside the input field */
        .message-input {
            display: flex;
            align-items: center;
            position: relative;
            padding-right: 40px;
            /* Make space for the icon */
        }

        .attachment-icon {
            position: absolute;
            right: 10px;
            /* Align it to the right */
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .attachment-icon i {
            font-size: 18px;
            color: #007bff;
            /* You can adjust the color */
        }

        /* Cross button styling */
        .clear-file {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 20px;
            color: red;
            display: none;
        }

        .message-input {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-message {
            padding-right: 30px;
            /* Adjust space for cross button */
        }

        .attachment-icon {
            cursor: pointer;
        }

        #chat-create-container {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        #user-select-container {
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        .select2 {
            width: 60%;
            max-width: 250px;
        }

        #group-name-section {
            display: none;
            /* Hidden by default */
            margin-top: 10px;
        }

        #group-name {
            width: 100%;
            max-width: 300px;
        }

        #chat-user-list {
            margin-top: 20px;
            /* Add space between the list and create form */
        }

        .call-video-container {
            display: flex;
            justify-content: center;
            /* Center the icons horizontally */
            align-items: center;
            /* Center the icons vertically */
            margin-top: 15px;
        }

        .video-call-icon,
        .call-icon {
            font-size: 30px;
            color: #007bff;
            /* Set the default color */
            cursor: pointer;
            margin: 0 15px;
            /* Space between icons */
            transition: color 0.3s ease-in-out, transform 0.3s ease;
            /* Smooth transitions */
        }

        .video-call-icon:hover,
        .call-icon:hover {
            color: #0056b3;
            /* Change color on hover */
            transform: scale(1.1);
            /* Slightly enlarge on hover */
        }

        .block-btn,
        .unblock-btn {
            font-size: 16px;
            margin-left: 10px;
        }

        .block-btn {
            background-color: #dc3545;
        }

        .unblock-btn {
            background-color: #28a745;
        }

        .block-btn:hover,
        .unblock-btn:hover {
            opacity: 0.8;
        }

        .no-user-message {
            text-align: center;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin: 20px;
            font-family: Arial, sans-serif;
        }

        .no-user-message h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .no-user-message p {
            color: #666;
            font-size: 16px;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="chat-container">
                        <div class="chat-sidebar">

                            <form method="POST" id="chat-form">
                                @csrf
                                <div id="chat-create-container">
                                    <!-- User Selection and Create Button -->
                                    <div id="user-select-container" class="d-flex align-items-center">
                                        <select class="form-control select2 w-100" name="users[]" id="user-dropdown"
                                            multiple required>
                                            <option value="">Select Users</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary ml-3"
                                            id="create-chat-btn">Create</button>
                                    </div>

                                    <!-- Group Name input (will only show when multiple users selected) -->
                                    <div id="group-name-section" class="mt-3" style="display: none;">
                                        <input type="text" id="group-name" class="form-control"
                                            placeholder="Enter Group Name">
                                    </div>
                                </div>
                            </form>

                            <!-- Chat Users List -->
                            <div class="user-list" id="chat-user-list">
                                @foreach ($chatUsers as $chatUser)
                                    <div class="user-item d-flex align-items-center p-3 mb-2 border rounded shadow-sm"
                                        data-id="{{ $chatUser['id'] }}">
                                        <img src="{{ isset($chatUser['photo']) && $chatUser['photo'] ? '/uploaded_files/' . $chatUser['photo'] : ($chatUser['is_group'] ? '/assets/project_images/chat/group.png' : '/assets/project_images/chat/user.png') }}"
                                            alt="User Image" class="user-image rounded-circle me-3"
                                            style="width: 40px; height: 40px; object-fit: cover;" />

                                        <!-- User Name and Latest Message -->
                                        <div class="d-flex flex-column flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="user-name fw-bold">{{ $chatUser['name'] }}</span>
                                            </div>

                                            @if ($chatUser['message'] != null)
                                                <div class="latest-message text-muted">
                                                    <span>{{ $chatUser['message'] }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Optional: Add a small indicator if this user is the active chat user -->
                                        <div class="ms-auto">

                                            <i class="fas fa-circle text-primary" onclick="test()"
                                                style="font-size: 12px; visibility: hidden;"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="chat-body">
                            <div class="chat-header">
                                <span id="chat-user-name" data-id="">Receiver Name</span>
                                <div class="chat-actions">
                                    <i class="fas fa-video video-call-icon" id="video_call"></i>
                                    <i class="fas fa-phone call-icon" id="call_icon"></i>

                                    <!-- Block button (Initially hidden) -->
                                    <button class="btn btn-sm btn-danger ms-3 action-btn block-btn" data-id=""
                                        style="display: none;">
                                        <i class="fas fa-ban"></i> Block
                                    </button>

                                    <!-- Unblock button (Initially hidden) -->
                                    <button class="btn btn-sm btn-success ms-3 action-btn unblock-btn" data-id=""
                                        style="display: none;">
                                        <i class="fas fa-unlock"></i> Unblock
                                    </button>
                                </div>
                            </div>


                            <div class="messages" id="chat-messages">
                                <div id="no-user-selected" class="no-user-message">
                                    <h2>Please Select a User for Messaging</h2>
                                    <p>Choose a user from the list to start a conversation.</p>
                                </div>
                            </div>
                            <form id="send-message">
                                <div class="message-input">
                                    <input type="text" name="message" id="message-input" placeholder="Type a message..."
                                        class="input-message">

                                    <label for="file-input" id="attachment-icon" class="attachment-icon">
                                        <i class="fas fa-paperclip"></i>
                                    </label>

                                    <input type="file" name="chat_files" id="file-input" class="file-input"
                                        style="display: none;">

                                    <div class="clear-file" id="clear-file-btn">
                                        <i class="fas fa-times"></i>
                                    </div>

                                    <button class="send-btn" type="submit">Send</button>
                                </div>
                            </form>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="audio-call-tab" style="display: none;">
        <div class="audio-call-container">
            <p id="call-user-name"></p>
            <button id="close-call">End Call</button>
        </div>
    </div>


    @include('chat.partials.audio-call-tab')

@endsection

@push('js')
    <script>
        $(document).ready(function() {

            $('.select2').select2({
                width: '100%'
            })

            let activeChatUserId = null;
            let refreshInterval = null;


            $("#user-dropdown").on("change", function() {
                const selectedUsers = $(this).val();
                if (selectedUsers && selectedUsers.length > 1) {
                    $("#group-name-section").show();
                } else {
                    $("#group-name-section").hide();
                }
            });


            function loadChatUsers() {
                $.ajax({
                    url: "{{ route('chat.index') }}",
                    type: "GET",
                    success: function(response) {
                        $("#chat-user-list").html(response.users);
                    },
                    error: function(xhr) {
                        console.error("Error loading users:", xhr.responseText);
                    }
                });
            }

            loadChatUsers();

            $("#chat-form").on("submit", function(event) {
                event.preventDefault();

                let selectedUsers = $("#user-dropdown").val();
                let groupName = $("#group-name")
                    .val();

                if (selectedUsers && selectedUsers.length > 0) {
                    $.ajax({
                        url: "{{ route('chat.startChatOrGroupChat') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            users: selectedUsers,
                            name: groupName || "",
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(xhr) {
                            console.error("Error starting chat:", xhr.responseText);
                        }
                    });
                }
            });



            $(document).on("click", ".user-item", function() {
                let userId = $(this).data("id");
                loadMessages(userId);
            });

            function loadMessages(userId) {

                $(".user-item").removeClass("active");
                $(`[data-id='${userId}']`).addClass("active");
                $.ajax({
                    url: "{{ route('chat.messages', ':userId') }}".replace(':userId', userId),
                    type: "GET",
                    success: function(response) {
                        // console.log(response);
                        $("#chat-messages").html(response.messages);

                        $("#chat-user-name")
                            .text(response.user_name)
                            .attr("data-id", userId);

                        let userIdInput = $("#send-message input[name='user_id']");
                        if (userIdInput.length) {
                            userIdInput.remove(); // Remove existing input
                        }
                        $("#send-message").append(
                            '<input type="hidden" id="user_id_input" name="user_id" value="' +
                            userId + '">'); // Add new one with ID

                        $("#no-user-selected").remove();

                        $('.user-block-id')
                            .attr("data-id", userId);

                        // Check if the user is blocked or not
                        if (!response.isGroup) {
                            if (response.isBlock) {
                                // Show unblock button for the sender if they blocked the other user
                                if (response.isSenderBlocked) {
                                    $(".unblock-btn").show();
                                    $(".block-btn").hide();
                                }
                                // Otherwise, hide both buttons
                                else {
                                    $(".block-btn").hide();
                                    $(".unblock-btn").hide();
                                }
                            } else {
                                // Show block button, hide unblock button if not blocked
                                $(".block-btn").show();
                                $(".unblock-btn").hide();
                            }
                        } else {
                            $(".block-btn").hide();
                            $(".unblock-btn").hide();
                        }
                    },
                    error: function(xhr) {
                        console.error("Error loading messages:", xhr.responseText);
                    }
                });
            }


            $("#send-message").on("submit", function(e) {
                e.preventDefault();

                let message = $("#message-input").val();
                let receiverId = $("#user_id_input").val();
                console.log(receiverId);
                let fileInput = $("#file-input")[0].files[0];
                if (!receiverId || receiverId === "" || (message.trim() === "" && !fileInput)) {
                    alert("Please select a user and enter a message or upload a file.");
                    return;
                }
                // alert(receiverId);
                let formData = new FormData();
                formData.append("_token", "{{ csrf_token() }}");
                formData.append("message", message);

                if (fileInput) {
                    formData.append("chat_files", fileInput);
                }

                $.ajax({
                    url: "{{ route('chat.send', ':receiverId') }}".replace(':receiverId',
                        receiverId),
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $("#chat-messages").append(response.message);
                            $("#message-input").val("");
                            $("#file-input").val("");
                        } else if (response.error) {
                            alert(response.error); // Display the error message
                        }
                    },
                    error: function(xhr) {
                        console.error("Error sending message:", xhr.responseText);
                    }
                });
            });

            $("#call_icon").on("click", function() {
                window.open("{{ route('chat.audioCall') }}", '_blank');
            });

            $("#video_call").on("click", function() {
                window.open("{{ route('chat.videoCall') }}", '_blank');
            });


            $("#open-modal-btn").on("click", function() {
                $("#createChatModal").modal("show");
            });

            $("#file-input").on("change", function() {
                if (this.files.length > 0) {
                    $("#attachment-icon").html('<i class="fas fa-trash"></i>');
                    $("#clear-file-btn").show();
                }
            });


            function clearFileInput() {
                $("#file-input").val("");
                resetAttachmentIcon();
                $("#clear-file-btn").hide();
            }


            $("#attachment-icon, #clear-file-btn").on("click", function() {
                clearFileInput();
            });


            function resetAttachmentIcon() {
                $("#attachment-icon").html('<i class="fas fa-paperclip"></i>');
            }


            $("#send-message").on("submit", function() {
                resetAttachmentIcon();
                $("#clear-file-btn").hide();
            });

            // Block button click event
            $(document).on('click', '.block-btn', function() {
                var userId = $("#chat-user-name").data(
                    "id"); // Get the user ID from the button's data-id attribute
                // Send AJAX request to block the user using the correct route URL
                $.ajax({
                    url: "{{ route('chat.block', ['receiverId' => 'USER_ID']) }}".replace(
                        'USER_ID', userId),
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            alert('User blocked successfully!');
                            // Hide block button and show unblock button
                            $(".block-btn[data-id='" + userId + "']").hide();
                            $(".unblock-btn[data-id='" + userId + "']").show();
                            location.reload();
                        } else {
                            alert('Failed to block the user.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error blocking user:", error);
                        alert('Error occurred while blocking the user.');
                    }
                });
            });


            // Unblock button click event
            $(document).on('click', '.unblock-btn', function() {
                var userId = $("#chat-user-name").data(
                    "id");

                // Send AJAX request to unblock the user using the correct route URL
                $.ajax({
                    url: "{{ route('chat.unblock', ['receiverId' => 'USER_ID']) }}".replace(
                        'USER_ID', userId),
                    type: 'POST',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            // Hide unblock button and show block button
                            $(".unblock-btn[data-id='" + userId + "']").hide();
                            $(".block-btn[data-id='" + userId + "']").show();
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        alert('An error occurred while trying to unblock the user.');
                    }
                });
            });


        });
    </script>
@endpush
