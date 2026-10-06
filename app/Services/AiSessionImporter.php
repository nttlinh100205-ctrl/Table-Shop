<?php
namespace App\Services;

use App\Models\{AiQuestionEvent, User};
use Illuminate\Support\Facades\{DB, Schema, Crypt, Log};
use Illuminate\Support\Str;

class AiSessionImporter
{
    public function run(bool $dryRun = false): array
    {
        $totals = ['sessions'=>0,'questions'=>0,'imported'=>0,'skipped'=>0,'failed'=>0];
        $consume = function ($id, $payload, $lastActivity, $userId = null) use (&$totals,$dryRun) {
            $totals['sessions']++;
            try {
                if (config('session.encrypt')) $payload = Crypt::decrypt($payload);
                $data = @unserialize($payload, ['allowed_classes'=>false]);
                if (!is_array($data)) $data = json_decode($payload, true);
                if (!is_array($data)) return;
                $result = $this->importSession($id, $data, $lastActivity, $userId, $dryRun);
                foreach ($result as $key=>$value) $totals[$key] += $value;
            } catch (\Throwable $e) {
                $totals['failed']++;
                Log::warning('AI session import skipped unreadable session', ['exception'=>get_class($e)]);
            }
        };
        $connection = DB::connection(config('session.connection'));
        $table = config('session.table','sessions');
        if ($connection->getSchemaBuilder()->hasTable($table)) {
            $connection->table($table)->select('id','payload','last_activity','user_id')->orderBy('id')->chunkById(100, function ($rows) use ($consume) {
                foreach ($rows as $row) {
                    $decoded = base64_decode($row->payload, true);
                    if ($decoded !== false) $consume($row->id,$decoded,$row->last_activity,$row->user_id);
                }
            }, 'id');
        }
        // Also recover file sessions left over from a previous session driver.
        $directory = config('session.files');
        if (is_dir($directory)) foreach (new \DirectoryIterator($directory) as $file) {
            if ($file->isFile() && !$file->isLink() && preg_match('/^[a-zA-Z0-9]{40}$/',$file->getFilename())) {
                $consume($file->getFilename(),file_get_contents($file->getPathname()),$file->getMTime());
            }
        }
        return $totals;
    }

    public function importSession(string $id, array $data, int $lastActivity, $userId = null, bool $dryRun = false): array
    {
        $result = ['questions'=>0,'imported'=>0,'skipped'=>0];
        $owner = $data['ai_transcript_owner'] ?? $userId;
        if (!$owner) foreach ($data as $key=>$value) {
            if (str_starts_with($key,'login_web_') && is_numeric($value)) { $owner = $value; break; }
        }
        if (is_numeric($owner) && User::find($owner)?->isAdmin()) return $result;
        $visitor = AiDemandAnalytics::visitorKey(is_numeric($owner) ? 'user:'.$owner : 'session:'.$id);
        $turns = $data['ai_transcript'] ?? null;
        if (!is_array($turns) || !$turns) {
            $turns = array_map(fn($turn)=>['text'=>$turn['text'] ?? '', 'user'=>($turn['role'] ?? '')==='user'], array_filter($data['ai_history'] ?? [], 'is_array'));
        }
        $live = Schema::hasColumn('ai_question_events','source') ? AiQuestionEvent::where('visitor_key',$visitor)->where('source','live')
            ->selectRaw('question_key, COUNT(*) as total')->groupBy('question_key')->pluck('total','question_key')->all() : [];
        $state = [];
        foreach ($turns as $index=>$turn) {
            if (!is_array($turn) || ($turn['user'] ?? false) !== true || !is_string($turn['text'] ?? null) || trim($turn['text'])==='') continue;
            $question = Str::limit($turn['text'],2000,'');
            $result['questions']++;
            $sourceKey = hash('sha256','session:'.$id.':'.$index.':'.$question);
            $text = Str::lower(Str::ascii($question));
            $product = ShopChatSupport::isTableAdviceStarter($question)
                || ChatProductSearch::isBudgetFollowUp($question,[],['chat_search_context'=>$state])
                || ChatProductSearch::isAttributeFollowUp($question,[],['chat_search_context'=>$state]);
            $answer = $turns[$index+1]['text'] ?? '';
            $kind = $answer === AiChatService::OUT_OF_SCOPE && !$product ? 'off_topic'
                : (preg_match('/don hang|van don|diem|thanh vien|voucher|giao hang|bao hanh|diem danh|lien he/', $text) ? 'support' : 'answer');
            if (is_string($answer) && preg_match('/^AI (?:đang hết hạn mức|phản hồi quá chậm|đang gặp lỗi|tạm thời không khả dụng)/u',$answer)) $kind = 'error';
            if ($kind === 'answer') $state = ShopChatSupport::isTableAdviceStarter($question) ? ChatProductSearch::criteria('bàn') : ChatProductSearch::criteria($question,[],$state);
            if (!$dryRun && AiQuestionEvent::where('source_key',$sourceKey)->exists()) { $result['skipped']++; continue; }
            $attributes = AiDemandAnalytics::attributes($question,$kind,$state,$visitor);
            $key = $attributes['question_key'];
            if (($live[$key] ?? 0)>0) { $live[$key]--; $result['skipped']++; continue; }
            if ($dryRun) continue;
            $at = \Carbon\Carbon::createFromTimestamp(min($lastActivity,time()),config('app.timezone'));
            AiQuestionEvent::firstOrCreate(['source_key'=>$sourceKey], $attributes+[
                'source'=>'session_import','created_at'=>$at,'updated_at'=>now(),
            ]);
            $result['imported']++;
        }
        return $result;
    }
}
