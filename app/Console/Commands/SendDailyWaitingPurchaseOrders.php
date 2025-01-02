<?php

namespace App\Console\Commands;

use App\Mail\DailyWaitingPurchaseOrders as DailyWaitingPurchaseOrdersMail;
use App\Models\WaitingPurchaseOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyWaitingPurchaseOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-waiting-purchase-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily email notifications for waiting purchase orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Query waiting purchase orders
        $waitingOrders = WaitingPurchaseOrder::where('status', 1)->get();

        if ($waitingOrders->isEmpty()) {
            $this->info('No waiting purchase orders found.');
            return 0;
        }

        // List of email addresses to notify
        $emails = ['tina@daijo.co.id', 'andriani@daijo.co.id', 'mumu@daijo.co.id']; // Replace with real emails

        // Send email to each address
        foreach ($emails as $email) {
            Mail::to($email)->send(new DailyWaitingPurchaseOrdersMail($waitingOrders));
        }

        $this->info('Daily waiting purchase order notifications sent successfully.');
        return 0;
    }
}
