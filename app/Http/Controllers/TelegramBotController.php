<?php

namespace App\Http\Controllers;

// use Telegram;
use Telegram\Bot\Laravel\Facades\Telegram;

/**
 * The Telegram Bot  Class                                         
 *+ 
 * @author John Muiruri  <jontedev@gmail.com>
 * 
 */
class TelegramBotController extends Controller
{
    /**
     * Get Updates(Messages from users or input) via Long polling
     * Cant work if a webhook is already setup
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
     * Register Webhook (eg. /start, /help)
     */
    public function tgWebhook()
    {
        $response = $update = Telegram::commandsHandler(true);
        return response()->json(['status' => 'success']);
    }

    /**
     * SetWebhook
     */
    public function runWebhook()
    {
        $url = 'https://tgytautopost.herokuapp.com/' . env('TELEGRAm_BOT_TOKEN') . '/webhook';
        $updates = Telegram::setWebhook(['url' => $url]);
        return response()->json($updates);
    }

    /**
     * Remove Webhook
     */
    public function removeWebhook()
    {
        $response = Telegram::removeWebhook();
        return response()->json($response);
    }
}
