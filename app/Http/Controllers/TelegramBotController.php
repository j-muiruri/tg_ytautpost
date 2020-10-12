<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\TelegramBot;
/**
 * The Telegram Bot  Class
 *
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
     * Get Webhook Updates
     */
    public function tgWebhook()
    {
        //$response = $update = Telegram::commandsHandler(true);
        Telegram::commandsHandler(true);
        // Telegram::getWebhookUpdates();
        // $update = new  Update;

        $this->saveUpdates();
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Set Webhook
     */
    public function runWebhook()
    {
        $url = env('APP_URL') . '/' . env('TELEGRAM_BOT_TOKEN') . '/webhook';
        $updates = Telegram::setWebhook(['url' => $url]);
        return response()->json($updates);
    }

    /**
     * Get Webhook Info
     */
    public function getWebhook()
    {
        $response = Telegram::getWebhookInfo();
        return response()->json($response);
    }

    /**
     * Remove Webhook
     */
    public function removeWebhook()
    {
        $response = Telegram::removeWebhook();
        return response()->json($response);
    }

    /**
    * Save Updates
    */
    public function saveUpdates()
    {
        //Get Json Update
        $data = Telegram::getWebhookUpdates();

        //Pluck Values
        $update_id = $data->update_id;
        $user_id = $data->message->from->id;
        $username = $data->message->from->username;
        $chat_id = $data->message->chat->update_id;
        $chat_type = $data->message->chat->chat_type;
        $message_id = $data->message->message_id;
        $message = $data->message->text;
        $entities = $data->message->entities;
        $message_type  = $entities->toArray();
        Log::debug($message_type);

        // Store messages in db

        TelegramBot::create(
            [
             'update_id' => $update_id,
             'user_id' => $user_id,
             'username' => $username,
             'chat_id' => $chat_id,
             'chat_type' => $chat_type,
             'message_id' => $message_id,
             'message' => $message,
             'message_type' => $message_type
         ]
        );
    }
}
