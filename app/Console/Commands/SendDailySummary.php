<?php

namespace App\Console\Commands;

use App\Mail\DailySummaryMail;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailySummary extends Command
{
    protected $signature = 'app:send-daily-summary';
    protected $description = 'Send daily expense summary via email';

    public function handle(WhatsAppService $wa)
    {
        $email = config('mail.from.address');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('MAIL_FROM_ADDRESS not configured.');
            return 1;
        }

        $summary = $wa->getDailySummary();
        $alerts = $wa->getBudgetAlerts();

        Mail::to($email)->send(new DailySummaryMail(
            summary: $summary,
            budgetAlerts: $alerts,
        ));

        $this->info('Daily summary sent to ' . $email);
        return 0;
    }
}
