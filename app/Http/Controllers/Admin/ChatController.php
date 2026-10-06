<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    /** Danh sách user đã từng nhắn với admin (+ tin chưa đọc) */
    public function getUsers()
    {
        $adminId = (int) Auth::id();

        // Lấy tin nhắn mới nhất giữa admin và từng khách hàng bằng subquery tối ưu
        $sub = Message::where(function ($q) use ($adminId) {
                $q->where('receiver_id', $adminId)->orWhere('sender_id', $adminId);
            })
            ->selectRaw('
                CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as other_id,
                MAX(id) as max_id
            ', [$adminId])
            ->groupBy('other_id');

        $latestMessages = Message::joinSub($sub, 'latest', function ($join) {
                $join->on('messages.id', '=', 'latest.max_id');
            })
            ->select('messages.id', 'messages.sender_id', 'messages.receiver_id', 'messages.content', 'messages.created_at', 'latest.other_id')
            ->get()
            ->keyBy('other_id');

        // Lấy tất cả khách hàng (ngoại trừ tài khoản admin hiện tại)
        $users = User::where('id', '!=', $adminId)
            ->where(function ($q) {
                $q->where('role', '!=', 'admin')->orWhereNull('role');
            })
            ->select('id', 'name', 'email', 'created_at')
            ->get();

        // Gộp thêm bất kỳ đối tác nào từng có tin nhắn (phòng trường hợp tài khoản đặc biệt)
        $extraIds = $latestMessages->keys()->filter(fn ($id) => (int) $id !== $adminId)->diff($users->pluck('id'));
        if ($extraIds->isNotEmpty()) {
            $extraUsers = User::whereIn('id', $extraIds)->select('id', 'name', 'email', 'created_at')->get();
            $users = $users->concat($extraUsers);
        }

        // Đếm tin chưa đọc từ mỗi user
        $unread = Message::where('receiver_id', $adminId)
            ->where('is_read', false)
            ->selectRaw('sender_id, COUNT(*) as cnt')
            ->groupBy('sender_id')
            ->pluck('cnt', 'sender_id');

        $result = $users->map(function ($u) use ($unread, $latestMessages) {
            $msg = $latestMessages->get($u->id);
            $u->unread = (int) ($unread[$u->id] ?? 0);
            $u->last_message = $msg ? $msg->content : null;
            $u->last_at = $msg ? $msg->created_at : null;
            return $u;
        })
        // Sắp xếp: Khách có tin nhắn mới nhất lên đầu, sau đó đến các khách chưa nhắn tin (ưu tiên mới tạo)
        ->sort(function ($a, $b) {
            if ($a->last_at && $b->last_at) {
                return $b->last_at <=> $a->last_at;
            }
            if ($a->last_at) return -1;
            if ($b->last_at) return 1;
            return $b->created_at <=> $a->created_at;
        })
        ->values();

        return response()->json($result);
    }

    /** Lịch sử chat với 1 user */
    public function getMessages(Request $request, $userId)
    {
        $adminId = Auth::id();
        $afterId = (int) $request->query('after_id', 0);

        // Đánh dấu đã đọc tin từ user
        Message::where('sender_id', $userId)
            ->where('receiver_id', $adminId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $query = Message::with('sender')
            ->where(function ($q) use ($userId, $adminId) {
                $q->where(function ($q2) use ($userId, $adminId) {
                    $q2->where('sender_id', $userId)->where('receiver_id', $adminId);
                })->orWhere(function ($q2) use ($userId, $adminId) {
                    $q2->where('sender_id', $adminId)->where('receiver_id', $userId);
                });
            });

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        }

        return response()->json($query->orderBy('id', 'asc')->get())->header('Cache-Control', 'no-store, private');
    }

    /** Admin gửi tin */
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->user_id,
            'content'     => $request->message,
            'is_read'     => true,
        ]);

        return response()->json($message->load('sender'));
    }

    /**
     * SSE stream cho admin — tin mới từ/đến user đang chọn.
     */
    public function stream(Request $request): StreamedResponse
    {
        $adminId = Auth::id();
        $userId  = (int) $request->query('user_id', 0);
        $lastId  = (int) $request->query('last_id', 0);

        return response()->stream(function () use ($adminId, $userId, $lastId) {
            $endAt = time() + 25;
            $cursor = $lastId;

            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            echo "event: connected\ndata: " . json_encode(['ok' => true, 'last_id' => $cursor]) . "\n\n";
            flush();

            while (time() < $endAt && connection_aborted() === 0) {
                if ($userId > 0) {
                    $messages = Message::with('sender')
                        ->where('id', '>', $cursor)
                        ->where(function ($q) use ($userId, $adminId) {
                            $q->where(function ($q2) use ($userId, $adminId) {
                                $q2->where('sender_id', $userId)->where('receiver_id', $adminId);
                            })->orWhere(function ($q2) use ($userId, $adminId) {
                                $q2->where('sender_id', $adminId)->where('receiver_id', $userId);
                            });
                        })
                        ->orderBy('id', 'asc')
                        ->limit(50)
                        ->get();

                    if ($messages->isNotEmpty()) {
                        foreach ($messages as $msg) {
                            $cursor = (int) $msg->id;
                            // Đánh dấu đã đọc nếu từ user
                            if ((int) $msg->sender_id === $userId && !(bool) $msg->is_read) {
                                Message::where('id', $msg->id)->update(['is_read' => true]);
                            }
                            echo "event: message\ndata: " . json_encode($msg) . "\n\n";
                        }
                        flush();
                    } else {
                        echo ": ping\n\n";
                        flush();
                    }
                } else {
                    // Không chọn user → chỉ ping + có thể báo user list refresh
                    $hasNew = Message::where('receiver_id', $adminId)
                        ->where('is_read', false)
                        ->where('id', '>', $cursor)
                        ->exists();
                    if ($hasNew) {
                        $maxId = (int) Message::where('receiver_id', $adminId)->max('id');
                        $cursor = max($cursor, $maxId);
                        echo "event: users_refresh\ndata: " . json_encode(['has_new' => true]) . "\n\n";
                        flush();
                    } else {
                        echo ": ping\n\n";
                        flush();
                    }
                }

                usleep(1500000);
            }

            echo "event: end\ndata: " . json_encode(['last_id' => $cursor]) . "\n\n";
            flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
