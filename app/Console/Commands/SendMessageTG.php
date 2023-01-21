<?php

namespace App\Console\Commands;

use App\Http\Controllers\WbOrdersController;
use App\Notifications\SendTGNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;

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
        $time = date('Y-m-d\TH:i:s\Z', time() - 86400);
        $wbOrders = (new WbOrdersController)
            ->find($time)
            ->collect()
            ->filter(fn($order) => $order['isCancel'] === false);

        if ($wbOrders->isEmpty()) {
            Log::warning($time);
            Log::warning('tg:msg no Orders');
            $this->warn('tg:msg no orders!');
            return Command::SUCCESS;
        }

        foreach ($wbOrders as $wbOrder) {
            if (!Redis::exists('wb_gNumber_' . $wbOrder['gNumber'])) {
                Redis::set('wb_gNumber_' . $wbOrder['gNumber'], true);
                $msg = "Поступил новый заказ - " . $wbOrder['gNumber'] . " https://seller.wildberries.ru/marketplace-orders-new/new-tasks/to-warehouse";

                Notification::route('telegram', 'TELEGRAM_CHAT_ID')
                    ->notify(new SendTGNotification($msg));
            }
        }

        $this->info('tg:msg works!');
        Log::info('tg:msg works');
        Log::info($time, $wbOrders->toArray());
        return Command::SUCCESS;
    }
}
