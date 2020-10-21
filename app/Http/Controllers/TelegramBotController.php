<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\TelegramBot;
use App\Http\Controllers\GoogleApiClientController as Google;
use Illuminate\Http\Request;
use Telegram\Bot\Commands\Command;

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

        $this->processUpdates();

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
     * Process Updates
     * @return true/false
     */
    public function processUpdates()
    {
        // sleep(1);
        $this->saveUpdates();

        $saveTokens = $this->saveTokens();

        $data = Telegram::getWebhookUpdates();

        $chat_id = $data->message->chat->id;
        $message_id = $data->message->message_id;
        $chatDetails['chat_id'] = $chat_id;
        $chatDetails['message_id'] = $message_id;

        // Check if tokens were generated
        if ($saveTokens === false) {

            //Error
            Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Authentication Error, Reply with /auth to grant Telegram Youtube Autopost Bot access'
            ]);

            $chatDetails['status'] = "failed";
            $this->updateStatus($chatDetails);
        } elseif (isset($saveTokens['auth'])) {

            //Success
            Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Authentication Successful!, Reply with /help for more commands to access your youtube content'
            ]);

            $chatDetails['status'] = "completed";
            $this->updateStatus($chatDetails);
        }
        return true;
    }

    /**
     * Save Updates
     * @return true
     */
    public function saveUpdates()
    {
        //Get Json Update
        $data = Telegram::getWebhookUpdates();

        //Pluck Values
        $update_id = $data->update_id;
        $user_id = $data->message->from->id;
        $username = $data->message->from->username;
        $chat_id = $data->message->chat->id;
        $chat_type = $data->message->chat->type;
        $message_id = $data->message->message_id;
        $message = $data->message->text;
        $entities = $data->message->entities;
        $message_type = $this->checkMessageType();
        // Log::debug($message_type);

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

        return true;
    }
    /**
     * Gets Previous Command
     * @return array $commandDetails
     */
    public function previousCommand()
    {
        $data = Telegram::getWebhookUpdates();

        $user_id = $data->message->from->id;
        $chat_id = $data->message->chat->id;

        $command = TelegramBot::select('message')
            ->where([
                ['user_id', '=', $user_id],
                ['chat_id', '=', $chat_id],
                ['message_type', '=', 'bot_command'],
            ])->orderBy('id', 'desc')
            ->first();
        $message = $command->message;

        $commandDetails = array();
        $commandDetails["message"] = $message;
        $commandDetails["chat_id"] = $chat_id;
        $commandDetails["user_id"] = $user_id;
        return $commandDetails;
    }
    /**
     * Check Type of Message
     * @return $message_type
     */
    public function checkMessageType()
    {

        $data = Telegram::getWebhookUpdates();

        $entities = $data->message->entities;
        if ($entities != null) {
            $object  = $entities->toArray();
            $entityArray = $object['0'];
            $message_type = $entityArray['type'];
            return $message_type;
        } else {
            $message_type = "normal_text";
            return $message_type;
        }
    }

    /**
     * Complete Auth to store Tokens
     * @return true/false
     * @return  array $data
     */
    public function saveTokens()
    {

        $message_type = $this->checkMessageType();

        $command = $this->previousCommand();

        if ($command["message"] === "/auth" && $message_type === "normal_text") {

            if ($this->generateTokens($command) === true) {
                Log::debug("Yeeeaa!!!!");
                $data['status'] = true;
                $data['auth'] = true;
                return $data;
            } else {
                Log::debug("Should be false");
                return false;
            }
        } else {
            return true;
        }
    }
    /**
     * Generate Access Tokens
     *  @return true/false
     */
    public function generateTokens(array $userDetails)
    {
        //Get the Code from db
        $data = TelegramBot::select('message')
            ->where([
                ['user_id', '=', $userDetails["user_id"]],
                ['chat_id', '=', $userDetails["chat_id"]],
                ['message_type', '=', 'normal_text'],
            ])->orderBy('id', 'desc')
            ->first();

        $code = $data->message;
        $client = new  Google;
        $saveTokens = $client->authSave($code, $userDetails);

        if ($saveTokens === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update Message and mark as Completed, failed, processing etc 
     *  @return true/false
     */
    public function updateStatus(array $userDetails)
    {
        try {
            TelegramBot::where([
                ['chat_id', $userDetails['chat_id']],
                ['message_id', $userDetails['message_id']]
            ])
                ->update(['status' => $userDetails['status']]);
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }

        return true;
    }
    /**
     * Check if User is subcriber
     * @return true/false
     */
    public function isSubscriber($user_id)
    {

            $userExists = TelegramBot::where(
                'user_id',
                '=',
                $user_id
            )->exists();
        if ($userExists != false) {
            return true;
        } else {
            return false;
        }
    }
}
