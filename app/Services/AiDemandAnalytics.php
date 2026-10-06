<?php
namespace App\Services;

use App\Models\AiQuestionEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiDemandAnalytics
{
    public const TOPICS = ['product'=>'Tìm sản phẩm', 'orders'=>'Đơn hàng', 'rewards'=>'Điểm, xu & ưu đãi', 'policies'=>'Dịch vụ & chính sách', 'general'=>'Câu hỏi khác về shop', 'off_topic'=>'Ngoài phạm vi', 'error'=>'AI gặp lỗi'];

    public static function redact(string $question): string
    {
        $question = strip_tags($question);
        $question = preg_replace('/[\w.+-]+@[\w.-]+\.[a-z]{2,}/iu', '[email]', $question);
        $question = preg_replace('/(?<!\d)(?:\+?84|0)(?:[ .-]?\d){8,10}(?!\d)/u', '[số điện thoại]', $question);
        $question = preg_replace('/\b(?:sk-|gsk_|AIza|AQ\.)[a-zA-Z0-9_.-]{16,}/', '[khóa đã ẩn]', $question);
        return Str::limit(trim(preg_replace('/\s+/u', ' ', $question)), 2000, '');
    }

    public static function record(Request $request, string $question, string $kind): void
    {
        // Analytics must never prevent the customer from receiving a chat reply.
        try {
            if ($request->user()?->isAdmin()) return;
            AiQuestionEvent::create(self::attributes($question, $kind, $request->session()->get('ai_search_context', []),
                self::visitorKey($request->user() ? 'user:'.$request->user()->id : 'session:'.$request->session()->getId())));
        } catch (\Throwable $e) {
            Log::warning('AI demand analytics unavailable', ['exception'=>get_class($e)]);
        }
    }

    public static function visitorKey(string $identity): string
    {
        return hash_hmac('sha256', $identity, (string)config('app.key'));
    }

    public static function attributes(string $question, string $kind, array $state, string $visitor): array
    {
            $safe = self::redact($question);
            $text = Str::lower(Str::ascii($safe));
            $topic = $kind;
            $criteria = null;
            $matches = null;
            if ($kind === 'support') {
                $topic = preg_match('/don hang|van don|giao hang|tra cuu/', $text) ? 'orders'
                    : (preg_match('/diem|hang thanh vien|xu|voucher|khuyen mai|khuyen ma[iy]|gioi thieu|vong quay/', $text) ? 'rewards'
                        : (preg_match('/con hang|ton kho|kich thuoc/', $text) ? 'product' : 'policies'));
            } elseif ($kind === 'answer') {
                $productText = Str::lower(Str::ascii(preg_replace('/\bbạn\b/ui', '', $safe)));
                $behavior = ['chat_search_context'=>$state];
                $topic = preg_match('/\b(san pham|ban|ghe|sofa|giuong|noi that)\b/', $productText)
                    || ChatProductSearch::isBudgetFollowUp($question, [], $behavior)
                    || ChatProductSearch::isAttributeFollowUp($question, [], $behavior) ? 'product' : 'general';
                if ($topic === 'product' && array_filter($state)) {
                    $criteria = $state;
                    // Use the same search as the chatbot, with the resolved follow-up filters.
                    $result = ChatProductSearch::search($question, ['chat_search_context'=>$criteria]);
                    $matches = collect($result['products'])->filter(fn($product)=>collect($product['variants'])->contains(
                        fn($variant)=>$variant['matches_detected_filters'] && ($variant['stock'] === null || $variant['stock'] > 0)
                    ))->count();
                }
            }
            $normalized = trim(preg_replace('/[^a-z0-9]+/', ' ', $text));
            return [
                'question'=>$safe, 'question_key'=>hash('sha256', $normalized),
                'visitor_key'=>$visitor,
                'topic'=>$topic, 'criteria'=>$criteria,
                'demand_key'=>$criteria ? hash('sha256', json_encode($criteria)) : null,
                'match_count'=>$matches,
            ];
    }

    public static function label(array $criteria): string
    {
        $labels = ['ban'=>'Bàn','ghe'=>'Ghế','sofa'=>'Sofa','giuong'=>'Giường','trang'=>'trắng','den'=>'đen','nau'=>'nâu','be'=>'be','xam'=>'xám','xanh'=>'xanh','do'=>'đỏ','vang'=>'vàng','hong'=>'hồng','kem'=>'kem','hien dai'=>'hiện đại','toi gian'=>'tối giản','co dien'=>'cổ điển','tan co dien'=>'tân cổ điển','bac au'=>'Bắc Âu'];
        $labels += ['ban an'=>'Bàn ăn','ban tra'=>'Bàn trà','ban van phong'=>'Bàn làm việc','ban cafe'=>'Bàn cafe'];
        $translate = fn($values)=>implode(', ', array_map(fn($value)=>$labels[$value] ?? $value, $values));
        $parts = [$translate($criteria['types'] ?? []) ?: 'Sản phẩm nội thất'];
        if ($criteria['colors'] ?? []) $parts[] = 'màu '.$translate($criteria['colors']);
        if ($criteria['styles'] ?? []) $parts[] = $translate($criteria['styles']);
        foreach ($criteria['dimensions_cm'] ?? [] as $axis=>$value) $parts[] = (['width'=>'rộng/dài','depth'=>'sâu','height'=>'cao'][$axis] ?? $axis).' '.$value.' cm';
        if ($budget = $criteria['budget_vnd'] ?? null) {
            $money = fn($n)=>number_format($n, 0, ',', '.').'đ';
            if ($budget['min'] == 0 && $budget['max'] !== null) $parts[] = ($budget['max_exclusive'] ? 'dưới ' : 'tối đa ').$money($budget['max']);
            elseif ($budget['max'] === null) $parts[] = ($budget['min_exclusive'] ? 'trên ' : 'từ ').$money($budget['min']);
            else $parts[] = $money($budget['min']).' – '.$money($budget['max']);
        }
        return implode(' · ', $parts);
    }
}
