<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    private function adminId(): int
    {
        $admin = User::where('role', 'admin')->orderBy('id')->first();

        return $admin ? (int) $admin->id : 1;
    }

    /** User gửi tin tới Admin */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);
        $messageText = trim($validated['message']);

        try {
            $message = Message::create([
                'sender_id'   => Auth::id(),
                'receiver_id' => $this->adminId(),
                'content'     => $messageText,
                'is_read'     => false,
            ]);

            $message->load('sender');

            return response()->json($message);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể gửi tin nhắn: ' . $e->getMessage()], 500);
        }
    }

    /** Lịch sử chat User ↔ Admin */
    public function getMessages(Request $request)
    {
        $userId  = Auth::id();
        $adminId = $this->adminId();
        $afterId = (int) $request->query('after_id', 0);

        $query = Message::with(['sender', 'receiver'])
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

        $messages = $query->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    /**
     * SSE stream — đẩy tin mới theo thời gian thực (poll DB mỗi 1.5s, giữ kết nối ~25s).
     */
    public function stream(Request $request): StreamedResponse
    {
        $userId  = Auth::id();
        $adminId = $this->adminId();
        $lastId  = (int) $request->query('last_id', 0);

        return response()->stream(function () use ($userId, $adminId, $lastId) {
            $endAt = time() + 25;
            $cursor = $lastId;

            // Disable buffering
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            echo "event: connected\ndata: " . json_encode(['ok' => true, 'last_id' => $cursor]) . "\n\n";
            if (ob_get_level()) {
                ob_flush();
            }
            flush();

            while (time() < $endAt && connection_aborted() === 0) {
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
                        echo "event: message\ndata: " . json_encode($msg) . "\n\n";
                    }
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                } else {
                    // keepalive
                    echo ": ping\n\n";
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                }

                usleep(1500000); // 1.5s
            }

            echo "event: end\ndata: " . json_encode(['last_id' => $cursor]) . "\n\n";
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
