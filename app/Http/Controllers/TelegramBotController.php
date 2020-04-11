<?php

namespace App\Http\Controllers;

use Telegram;

class TelegramBotController extends Controller
{
    /**
     * Get Updates(Messages from users or input)
     */
    public function updatedActivity()
    {
        $activity = Telegram::getUpdates();
        return response()->json($activity);
    }
    /**
     * Run Commands (eg. /start, /help)
     */
    public function runCommands()
    {
        $update = Telegram::commandsHandler(false, ['timeout' => 0]);
        return response()->json(['status' => 'success']);
    }
    /**
     * Register Webhok (eg. /start, /help)
     */
    public function tgWebhook()
    {
        $response = $update = Telegram::commandsHandler(true);
        return response()->json(['status' => 'success']);
        
    }

    /**
     * Run Webhook
     */
    public function runWebhook()
    {
            $url = 'https://tgytautopost.herokuapp.com/'.env('TELEGRAM_BOT_TOKEN') .'webhook';
            $updates = Telegram::setWebhook(['url' => $url]);
            return response()->json($updates);
    }
}
