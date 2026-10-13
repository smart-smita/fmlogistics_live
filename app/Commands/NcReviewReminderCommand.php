<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class NcReviewReminderCommand extends BaseCommand
{
    protected $group       = 'Cron';
    protected $name        = 'cron:nc-reminders';
    protected $description = 'Dispatches 24-hour pending NC review reminders for latest OE, HSE, and Gemba audits with cross-module deduplication.';

    public function run(array $params)
    {
        CLI::write('Executing 24-hour Pending NC Review Reminders (OE, HSE, Gemba)...', 'yellow');

        $cronController = new \App\Controllers\Cron();
        $cronController->pendingNcReviewReminder();

        CLI::write('Cron execution complete.', 'green');
    }
}
