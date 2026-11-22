@foreach ($messages as $message)
@if ($message->sender_id != user()->id)
<div class="message received">
    <p class="message-text">{{ $message->message }}</p>
    <span class="message-time">{{ $message->created_at->format('H:i') }}</span>
</div>
@else
<div class="message sent">
    <p class="message-text">{{ $message->message }}</p>
    <span class="message-time">{{ $message->created_at->format('H:i') }}</span>
</div>
@endif
@endforeach