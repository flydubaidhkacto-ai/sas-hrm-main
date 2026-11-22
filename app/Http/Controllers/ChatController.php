<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatBlock;
use App\Models\ChatMember;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    /**
     * Display chat users list.
     */
    public function index()
    {
        $authUserId = Auth::id();

        $users = User::active_employees()
            ->where('id', '!=', $authUserId)
            ->where('employee_type', '!=', 'Probationary')
            ->get();

        $chatUsers = User::whereIn('id', function ($query) use ($authUserId) {
            $query->select('sender_id')
                ->from('chats')
                ->where('receiver_id', $authUserId)
                ->whereNotNull('sender_id');
        })
            ->orWhereIn('id', function ($query) use ($authUserId) {
                $query->select('receiver_id')
                    ->from('chats')
                    ->where('sender_id', $authUserId)
                    ->whereNotNull('receiver_id');
            })
            ->get()
            ->map(function ($user) use ($authUserId) {
                $latestMessage = ChatMessage::whereIn('chat_id', function ($query) use ($authUserId, $user) {
                    $query->select('id')
                        ->from('chats')
                        ->where(function ($q) use ($authUserId, $user) {
                            $q->where('sender_id', $authUserId)
                                ->where('receiver_id', $user->id);
                        })
                        ->orWhere(function ($q) use ($authUserId, $user) {
                            $q->where('sender_id', $user->id)
                                ->where('receiver_id', $authUserId);
                        });
                })
                    ->latest('created_at')
                    ->first();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'photo' => $user->picture_file,
                    'is_group' => false,
                    'message' => $latestMessage ? $latestMessage->message : null,
                    'created_at' => $latestMessage ? $latestMessage->created_at : null,
                ];
            })
            ->toArray();

        $groupChats = Chat::where('is_group', true)
            ->whereIn('id', function ($query) use ($authUserId) {
                $query->select('chat_id')
                    ->from('chat_members')
                    ->where('user_id', $authUserId);
            })
            ->get(['id', 'name'])
            ->map(function ($group) {
                $latestMessage = ChatMessage::where('chat_id', $group->id)
                    ->latest('created_at')
                    ->first();

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'photo' => null,
                    'is_group' => true,
                    'message' => $latestMessage ? $latestMessage->message : null,
                    'created_at' => $latestMessage ? $latestMessage->created_at : null,
                ];
            })
            ->toArray();

        $chatUsers = array_merge($chatUsers, $groupChats);

        usort($chatUsers, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return view('chat.index', compact('users', 'chatUsers'));
    }

    /**
     * Get messages between logged-in user and selected user or group.
     */
    public function getMessages($receiverId)
    {
        $senderId = Auth::id();

        $chat = Chat::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                ->where('receiver_id', $receiverId);
        })
            ->orWhere(function ($query) use ($senderId, $receiverId) {
                $query->where('sender_id', $receiverId)
                    ->where('receiver_id', $senderId);
            })
            ->first();

        if ($chat) {
            $chatBlock = ChatBlock::where('chat_id', $chat->id)->first();
            $isBlock = $chatBlock ? true : false;
            $isSenderBlocked = $chatBlock && $chatBlock->blocked_by_id == $senderId;
        }

        if (!$chat) {
            $chat = Chat::where('id', $receiverId)->where('is_group', true)->first();
        }

        $messages = [];
        if ($chat) {
            $messages = ChatMessage::where('chat_id', $chat->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        $isGroup = false;
        if ($chat && $chat->is_group) {
            $receiverName = $chat->name;
            $isGroup = true;
        } else {
            $receiver = User::find($receiverId);
            $receiverName = $receiver ? $receiver->name : 'Unknown User';
        }

        return response()->json([
            'messages' => view('chat.partials.messages', compact('messages', 'receiverName'))->render(),
            'user_name' => $receiverName,
            'isBlock' => $isBlock ?? false,
            'isSenderBlocked' => $isSenderBlocked ?? false,
            'isGroup' => $isGroup,
        ]);
    }




    /**
     * Store a newly created message.
     */
    public function sendMessage(Request $request, $receiverId)
    {
        $request->validate([
            'message' => 'nullable|string',
            'chat_files' => 'nullable|file|max:2048',
        ]);
        $senderId = Auth::id();

        // chat one to one message
        $chat = Chat::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)->where('receiver_id', $senderId);
        })->first();


        // check if group message
        if (!$chat) {
            $chat = Chat::where('id', $receiverId)->where('is_group', true)->first();
        }

        $chatBlock = ChatBlock::where('chat_id', $chat->id)->first();
        if ($chatBlock) {
            return response()->json([
                'success' => false,
                'error' => $chatBlock->blocked_by_id == $senderId ? 'Unblocked the user' : 'This user has blocked you'
            ]);
        }

        $filePath = null;
        if ($request->hasFile('chat_files')) {
            $originalName = $request->file('chat_files')->getClientOriginalName();
            $filePath = $request->file('chat_files')->storeAs('chat', $originalName);
        }

        $message = new ChatMessage();
        $message->chat_id = $chat->id;
        $message->sender_id = $senderId;
        $message->message = $request->message ?? '';
        $message->file_path = $filePath;
        $message->save();


        if ($chat->is_group) {
            $members = ChatMember::where('chat_id', $chat->id)->get();

            foreach ($members as $member) {
                // You can implement logic here to notify members (e.g., via broadcasting, websockets)
            }
        }

        $messageHtml = view('chat.partials.message_item', compact('message'))->render();

        return response()->json([
            'success' => true,
            'message' => $messageHtml
        ]);
    }



    public function getAudioCall()
    {
        return view('chat.audio_call');
    }

    public function getVideoCall(Request $request)
    {
        // Cache::forget('webrtc_signaling');
        // Cache::forget('webrtc_signaling2');
        return view('chat.video_call2');
    }



    public function startChatOrGroupChat(Request $request)
    {
        $request->validate([
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ]);

        $authUserId = user()->id;

        $isGroupChat = count($request->users) > 1;

        if ($isGroupChat) {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            if (!in_array($authUserId, $request->users)) {
                $request->merge([
                    'users' => array_merge([$authUserId], $request->users)
                ]);
            }

            $chat = Chat::create([
                'name' => $request->name,
                'is_group' => true,
            ]);

            foreach ($request->users as $userId) {
                ChatMember::create([
                    'chat_id' => $chat->id,
                    'user_id' => $userId,
                    'is_admin' => $userId == $authUserId,
                ]);
            }
        } else {
            $receiverId = $request->users[0];

            $chat = Chat::where(function ($query) use ($authUserId, $receiverId) {
                $query->where('sender_id', $authUserId)
                    ->where('receiver_id', $receiverId);
            })->orWhere(function ($query) use ($authUserId, $receiverId) {
                $query->where('sender_id', $receiverId)
                    ->where('receiver_id', $authUserId);
            })->first();
            if (!$chat) {
                $chat = new Chat();
                $chat->sender_id = $authUserId;
                $chat->receiver_id = $receiverId;
                $chat->is_group = false;
                $chat->name = null;
                $chat->save();
            }
        }
        // dd('hi');
        $chatUsers = User::whereIn('id', function ($query) use ($chat) {
            $query->select('receiver_id')
                ->from('chats')
                ->where('sender_id', $chat->sender_id)
                ->orWhere('receiver_id', $chat->sender_id);
        })->get();

        return response()->json([
            'chat_id' => $chat->id,
            'name' => $chat->name,
            'users' => view('chat.partials.user_list', compact('chatUsers'))->render(),
            'messages' => view('chat.partials.messages', ['messages' => $chat->messages])->render(),
        ]);
    }

    public function block(Request $request, int $receiverId)
    {
        $senderId = Auth::id();
        $chat = Chat::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)
                ->where('receiver_id', $senderId);
        })->first();

        if (!$chat) {
            return response()->json(['error' => 'User not found invalid']);
        }

        ChatBlock::create([
            'chat_id' => $chat->id,
            'blocked_by_id' => $senderId,
        ]);

        return response()->json(['success' => true, 'message' => 'User blocked successfully']);
    }

    // In your Controller
    public function unblockUser(Request $request, $receiverId)
    {
        $senderId = Auth::id();

        // Find the chat between the sender and receiver
        $chat = Chat::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                ->where('receiver_id', $receiverId);
        })
            ->orWhere(function ($query) use ($senderId, $receiverId) {
                $query->where('sender_id', $receiverId)
                    ->where('receiver_id', $senderId);
            })
            ->first();

        if ($chat) {
            // Check if the sender blocked the receiver
            $chatBlock = ChatBlock::where('chat_id', $chat->id)
                ->where('blocked_by_id', $senderId)
                ->first();

            if ($chatBlock) {
                // Remove the block
                $chatBlock->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'User successfully unblocked.',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'User is not blocked.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Chat not found.',
        ]);
    }
}
