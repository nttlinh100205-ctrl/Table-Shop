<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AiQuestionEvent, AiDemandPlan};
use App\Services\AiDemandAnalytics;
use Illuminate\Http\Request;

class AiDemandController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['days'=>'nullable|in:7,30,90', 'topic'=>'nullable|in:'.implode(',', array_keys(AiDemandAnalytics::TOPICS))]);
        $days = (int)($data['days'] ?? 30);
        $since = now()->timezone('Asia/Ho_Chi_Minh')->startOfDay()->subDays($days - 1)->setTimezone(config('app.timezone'));
        $base = AiQuestionEvent::where('created_at', '>=', $since);
        $total = (clone $base)->count();
        $visitors = (clone $base)->distinct()->count('visitor_key');
        $gaps = (clone $base)->where('topic', 'product')->where('match_count', 0)->count();
        $topics = (clone $base)->selectRaw('topic, COUNT(*) as total')->groupBy('topic')->orderByDesc('total')->get();
        $questions = (clone $base)->when($data['topic'] ?? null, fn($q,$topic)=>$q->where('topic',$topic))
            ->selectRaw('question_key, topic, MIN(question) as question, COUNT(*) as total, COUNT(DISTINCT visitor_key) as visitors, MAX(created_at) as last_at')
            ->groupBy('question_key', 'topic')->orderByDesc('total')->orderByDesc('last_at')->orderBy('question_key')->paginate(15)->withQueryString();
        $demands = (clone $base)->where('topic','product')->whereNotNull('demand_key')
            ->selectRaw('demand_key, MIN(id) as sample_id, COUNT(*) as total, COUNT(DISTINCT visitor_key) as visitors, SUM(CASE WHEN match_count = 0 THEN 1 ELSE 0 END) as missing')
            ->groupBy('demand_key')->orderByDesc('missing')->orderByDesc('visitors')->orderByDesc('total')->limit(10)->get();
        $samples = AiQuestionEvent::whereIn('id', $demands->pluck('sample_id'))->get()->keyBy('id');
        $plans = AiDemandPlan::whereIn('demand_key', $demands->pluck('demand_key'))->get()->keyBy('demand_key');
        foreach ($demands as $demand) {
            $demand->label = AiDemandAnalytics::label($samples[$demand->sample_id]->criteria);
            $demand->plan = $plans[$demand->demand_key] ?? null;
        }
        return response()->view('admin.ai-demands.index', compact('days','total','visitors','gaps','topics','questions','demands'))
            ->header('Cache-Control','no-store, private');
    }

    public function update(Request $request, string $key)
    {
        abort_unless(AiQuestionEvent::where('demand_key',$key)->where('topic','product')->exists(), 404);
        $data = $request->validate(['status'=>'required|in:reviewing,planned,done,dismissed','note'=>'nullable|string|max:1000']);
        AiDemandPlan::updateOrCreate(['demand_key'=>$key], $data);
        return back()->with('success','Đã lưu kế hoạch cho nhu cầu này.');
    }
}
