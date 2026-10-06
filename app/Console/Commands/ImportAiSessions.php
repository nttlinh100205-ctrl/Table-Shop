<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiSessionImporter;

class ImportAiSessions extends Command
{
    protected $signature = 'ai:import-sessions {--dry-run : Inspect counts without writing analytics}';
    protected $description = 'Recover AI questions from remaining sessions without duplicating live analytics';
    public function handle(AiSessionImporter $importer): int
    {
        $result = $importer->run((bool)$this->option('dry-run'));
        $this->line(json_encode($result));
        return $result['failed'] ? 1 : 0;
    }
}
