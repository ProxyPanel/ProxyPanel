<?php

namespace App\Console\Commands;

use App\Models\JangoPayAttempt;
use App\Services\JangoPay\Reconciliation;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class JangoPayReconcile extends Command
{
    protected $signature = 'jangopay:reconcile {--attempt= : Check one saved attempt} {--limit=25 : Maximum attempts per run}';

    protected $description = 'Recover JangoPay payments through authenticated reads without repeating invoice/coin POSTs';

    public function handle(Reconciliation $reconciliation): int
    {
        $query = JangoPayAttempt::query();
        if ($this->option('attempt') !== null) {
            if (! ctype_digit((string) $this->option('attempt'))) {
                $this->error('Invalid attempt ID', OutputInterface::VERBOSITY_NORMAL);

                return self::FAILURE;
            }
            $query->whereKey((int) $this->option('attempt'));
        } else {
            $query->whereNotIn('state', ['delivered', 'resolved']);
        }
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
        if ($limit === false) {
            $this->error('Limit must be 1..100', OutputInterface::VERBOSITY_NORMAL);

            return self::FAILURE;
        }
        $attempts = $query->orderByRaw('last_checked_at IS NOT NULL')->orderBy('last_checked_at')->orderBy('id')->limit($limit)->get();
        if ($this->option('attempt') !== null && $attempts->isEmpty()) {
            $this->error('Attempt not found', OutputInterface::VERBOSITY_NORMAL);

            return self::FAILURE;
        }
        foreach ($attempts as $attempt) {
            $this->line($attempt->id.': '.$reconciliation->check($attempt->id), null, OutputInterface::VERBOSITY_NORMAL);
        }

        return self::SUCCESS;
    }
}
