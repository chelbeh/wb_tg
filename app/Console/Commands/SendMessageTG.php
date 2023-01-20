<?php

namespace App\Console\Commands;

use App\Http\Controllers\TelegramController;
use App\Http\Controllers\WbOrdersController;
use App\Notifications\SendTGNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendMessageTG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tg:msg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send message to telegram bot';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $wbOrders = (new WbOrdersController)
            ->find(date('Y-m-d\TH:i:s\Z', time() - 10800))
            ->collect()
            ->filter(fn($order) => $order['isCancel'] === false);

        if ($wbOrders->isEmpty()) {
            Log::warning('tg:msg no Orders');
            $this->warn('tg:msg no orders!');
            return Command::SUCCESS;
        }

        $msg = 'Поступил новый заказ https://seller.wildberries.ru/marketplace-orders-new/new-tasks/to-warehouse';

        Notification::route('telegram', 'TELEGRAM_CHAT_ID')
            ->notify(new SendTGNotification($msg));
        $this->info('tg:msg works!');
        Log::info('tg:msg works');

        return Command::SUCCESS;
    }
}
