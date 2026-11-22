@foreach ($chatUsers as $chatUser)
    <li class="user-item" onclick="loadMessages({{ $chatUser->id }})">
        <div class="user-status"></div>
        <span class="user-name">{{ $chatUser->name }}</span>
    </li>
@endforeach
