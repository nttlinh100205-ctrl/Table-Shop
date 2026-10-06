<?php
namespace App\Jobs;

use App\Models\Review;
use App\Services\ReviewReplyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReplyToReview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public int $timeout = 45;
    public array $backoff = [30, 120, 300];

    public function __construct(public int $reviewId)
    {
        $connection = config('mail.verification_queue_connection', 'database');
        $this->onConnection($connection === 'sync' ? 'database' : $connection);
        $this->onQueue('ai-reviews');
        $this->afterCommit();
    }

    public function handle(ReviewReplyService $service): void
    {
        $review = Review::with('product')->find($this->reviewId);
        if (!$review || $review->admin_reply !== null || $review->resolution_status === 'resolved') return;
        $answer = $service->generate($review);
        if (trim($answer) === '') throw new \RuntimeException('Empty review response');
        // Atomic compare-and-set: retries and a concurrent admin reply cannot overwrite a response.
        Review::whereKey($review->id)->whereNull('admin_reply')->where('resolution_status', 'pending')
            ->where('comment', $review->comment)->where('rating', $review->rating)
            ->update(['admin_reply'=>$answer, 'replied_at'=>now(), 'reply_source'=>'ai', 'ai_reply_status'=>'completed']);
    }

    public function failed(?\Throwable $exception): void
    {
        Review::whereKey($this->reviewId)->whereNull('admin_reply')->update(['ai_reply_status'=>'failed']);
    }
}
