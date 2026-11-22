@foreach ($messages as $message)
<div class="message-container {{ $message->sender_id == user()->id ? 'sent-message' : 'received-message' }}">
    <div class="message-content">
        @if ($message->message)
        <p class="message-text">{{ $message->message }}</p>
        @endif
        @if ($message->file_path)
        <div class="message-file">
            <a href="{{ asset('uploaded_files/' . $message->file_path) }}"
                download="{{ asset('uploaded_files/' . $message->file_path) }}">
                <i class="fas fa-file"></i> {{ $message->file_name }}
            </a>
        </div>
        @endif
    </div>
    <div class="message-meta">
        <span class="message-time">{{ $message->created_at->format('h:i A') }}</span>
    </div>
</div>
@endforeach