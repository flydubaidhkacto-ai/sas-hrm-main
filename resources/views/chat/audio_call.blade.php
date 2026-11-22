<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Audio Call</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #121212;
            /* Dark background */
            color: white;
            font-family: Arial, sans-serif;
        }

        .call-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            position: relative;
            padding: 10px;
        }

        .call-header {
            position: absolute;
            top: 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 10px;
            z-index: 10;
        }

        .call-header .user-name {
            font-size: 18px;
            font-weight: bold;
        }

        .call-header .close-btn {
            background-color: transparent;
            border: none;
            color: #ff5c5c;
            font-size: 20px;
            cursor: pointer;
        }

        .call-header .close-btn:hover {
            color: #e74c3c;
        }

        .call-body {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #333;
            width: 100%;
            height: 85%;
            /* Adjust the height for the body to take up most of the space */
            border-radius: 8px;
            margin-top: 50px;
            /* Adjust for header space */
        }

        .call-footer {
            position: absolute;
            bottom: 20px;
            display: flex;
            justify-content: center;
            /* Center the buttons together */
            align-items: center;
            /* Vertically align the buttons in the center */
            width: 100%;
            z-index: 10;
        }

        .call-footer button {
            padding: 15px;
            font-size: 20px;
            background-color: #444;
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            width: 60px;
            height: 60px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            margin: 0 10px;
            /* Space between the buttons */
        }

        .call-footer .end-call-btn {
            background-color: #ff5c5c;
        }

        .call-footer .end-call-btn:hover {
            background-color: #e74c3c;
        }

        .call-footer .mute-btn {
            background-color: #00c853;
        }

        .call-footer .mute-btn:hover {
            background-color: #00b34d;
        }

        .call-footer .video-btn {
            background-color: #ffeb3b;
        }

        .call-footer .video-btn:hover {
            background-color: #fdd835;
        }
    </style>
</head>

<body>
    <div class="call-container">
        <!-- Header -->
        <div class="call-header">
            <div class="user-name" id="call-user-name">Receiver Name</div>
            <button class="close-btn" id="close-call"><i class="fas fa-times"></i></button>
        </div>

        <!-- Body (Audio Call Placeholder) -->
        <div class="call-body">
            <div>Audio Call in Progress...</div>
        </div>

        <div class="call-footer">
            <button class="mute-btn" id="mute-btn">
                <i class="fas fa-microphone"></i> <!-- Mute Icon -->
            </button>
            <button class="end-call-btn" id="end-call-btn">
                <i class="fas fa-phone-slash"></i> <!-- End Call Icon -->
            </button>
            <button class="video-btn" id="video-btn">
                <i class="fas fa-video"></i> <!-- Video Icon -->
            </button>
        </div>


    </div>

    <script>
        // JavaScript to handle button clicks and logic
        document.getElementById('close-call').addEventListener('click', function() {
            window.close(); // Close the window
        });

        document.getElementById('end-call-btn').addEventListener('click', function() {
            alert('Call ended');
            // Add functionality to end the actual call here
            window.close(); // Close the window after the call is ended
        });

        document.getElementById('mute-btn').addEventListener('click', function() {
            alert('Mute button clicked');
            // Add logic to mute/unmute the audio here
        });

        document.getElementById('video-btn').addEventListener('click', function() {
            alert('Switch to video call');
            // Add logic to switch to video call if needed
        });
    </script>
</body>

</html>
