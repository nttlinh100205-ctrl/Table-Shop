<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\AiChatService;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    private function transcript(Request $request): array
    {
        $owner = (string) ($request->user()?->id ?? 'guest');
        if ($request->session()->has('ai_transcript_owner') && $request->session()->get('ai_transcript_owner') !== $owner) {
            $request->session()->forget(['ai_transcript', 'ai_history', 'ai_search_context']);
        }
        $request->session()->put('ai_transcript_owner', $owner);
        if (!$request->session()->has('ai_transcript')) {
            $request->session()->put('ai_transcript', [[
                'text' => AiChatService::getInitialGreeting($request->session()->get('shopping_behavior')),
                'user' => false,
            ]]);
        }
        return $request->session()->get('ai_transcript');
    }

    private function reply(Request $request, string $message, string $reply, int $status = 200, ?string $code = null)
    {
        // Display history is separate from model context: private order/account replies never go to AI.
        $messages = $this->transcript($request);
        $messages[] = ['text' => $message, 'user' => true];
        $messages[] = ['text' => $reply, 'user' => false];
        $request->session()->put('ai_transcript', $messages);
        $data = $status === 200 ? ['reply' => $reply] : ['message' => $reply, 'code' => $code];
        return response()->json($data + ['messages' => $messages], $status)->header('Cache-Control', 'no-store, private');
    }

    public function greeting(Request $request)
    {
        $messages = $this->transcript($request);
        return response()->json(['reply' => $messages[0]['text'], 'messages' => $messages])->header('Cache-Control', 'no-store, private');
    }
    public function send(Request $request)
    {
        $data = $request->validate(['message' => 'required|string|max:2000']);
        $this->transcript($request);
        $support = \App\Services\ShopChatSupport::reply($data['message'], $request->user());
        if ($support !== null) return $this->reply($request, $data['message'], $support);
        $history = $request->session()->get('ai_history', []);
        $behavior = $request->session()->get('shopping_behavior', []);
        $behavior['chat_search_context'] = $request->session()->get('ai_search_context', []);
        try {
            $reply = AiChatService::chat($data['message'], $behavior, $history);
        } catch (\App\Exceptions\AiUnavailableException $e) {
            return $this->reply($request, $data['message'], $e->getMessage(), 503, $e->reason);
        } catch (\RuntimeException $e) {
            return $this->reply($request, $data['message'], 'AI tạm thời không khả dụng. Vui lòng thử lại.', 503, 'AI_UNAVAILABLE');
        }
        if ($reply === AiChatService::OUT_OF_SCOPE) {
            return $this->reply($request, $data['message'], $reply);
        }
        $request->session()->put('ai_search_context', \App\Services\ChatProductSearch::criteria($data['message'], $history, $behavior['chat_search_context']));
        $history[] = ['role' => 'user', 'text' => $data['message']];
        $history[] = ['role' => 'model', 'text' => $reply];
        $request->session()->put('ai_history', array_slice($history, -6));
        return $this->reply($request, $data['message'], $reply);
    }
}
