<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\AiChatService;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function greeting(Request $request)
    {
        return response()->json(['reply' => AiChatService::getInitialGreeting($request->session()->get('shopping_behavior'))]);
    }
    public function send(Request $request)
    {
        $data = $request->validate(['message' => 'required|string|max:2000']);
        $history = $request->session()->get('ai_history', []);
        try {
            $reply = AiChatService::chat($data['message'], $request->session()->get('shopping_behavior'), $history);
        } catch (\App\Exceptions\AiUnavailableException $e) {
            return response()->json(['message'=>$e->getMessage(),'code'=>$e->reason],503);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }
        if ($reply === AiChatService::OUT_OF_SCOPE) {
            return response()->json(['reply' => $reply]);
        }
        $history[] = ['role' => 'user', 'text' => $data['message']];
        $history[] = ['role' => 'model', 'text' => $reply];
        $request->session()->put('ai_history', array_slice($history, -6));
        return response()->json(['reply' => $reply]);
    }
}
