<div class="message-container {{ $message->sender_id == user()->id ? 'sent-message' : 'received-message' }}">
    <div class="message-content">
        @if ($message->message)
        <p class="message-text">{{ $message->message }}</p>
        @endif

        @if ($message->file_path)
        @php
        $fileName = basename($message->file_path);
        @endphp
        <div class="file-message">
            <a href="{{ asset('uploaded_files/chat/' . $fileName) }}"
                download="{{ asset('uploaded_files/chat/' . $fileName) }}" class="file-link">
                📄 {{ $fileName }}
            </a>
        </div>
        @endif

        <span class="message-time">{{ $message->created_at->format('h:i A') }}</span>
    </div>
</div>