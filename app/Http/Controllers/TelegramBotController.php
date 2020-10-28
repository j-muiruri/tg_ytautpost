<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\TelegramBot;
use App\Http\Controllers\GoogleApiClientController as Google;
use App\Subscribers;
use Illuminate\Http\Request;
use Telegram\Bot\Commands\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

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

        response()->json(['status' => 'success']);
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

        //Get Telegram Updates
        $data = Telegram::getWebhookUpdates();

        logger($data);

        //Save Updates to db
        $this->saveUpdates();

        $isInlineQuery = $this->isInlineQuery();

        // Check if Update is a message or inline query, process message
        if ($isInlineQuery === false) {

            $message_type = $this->checkMessageType();

            switch ($message_type) {

                //Process normal message
                case 'normal_text':
                    $this->processNormalMessage();
                    break;
                
                default:
                    return true;
                    break;
            }
        }

        return true;
    }

    /**
     * Check If this is an Inline query 
     * @return true/false Returns true if update is inline Query an d false if it is a message or bot_command
     */
    public function isInlineQuery()
    {
        $data = Telegram::getWebhookUpdates();

        $array = $data->message;

        //Check if the key message exists in the array, if true, update is not an inline query, else it is an inline query
        if ($array === null) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Save Updates
     * @return true Returns true on saving
     */
    public function saveUpdates()
    {
        //Get Json Update
        $data = Telegram::getWebhookUpdates();

        $array = (array) $data;

        // $updateIsMessage = isset($data->message->message_id);

        logger($data->message);
        if ($data->message != null) {

            logger("this is a message");
            //Pluck Values
            $update_id = $data->update_id;
            $user_id = $data->message->from->id;
            $username = $data->message->from->username;
            $chatId = $data->message->chat->id;
            $chat_type = $data->message->chat->type;
            $message_id = $data->message->message_id;
            $message = $data->message->text;
            $entities = $data->message->entities;
            $message_type = $this->checkMessageType();
            // logger($message_type);

            // Store messages in db

            TelegramBot::create(
                [
                    'update_id' => $update_id,
                    'user_id' => $user_id,
                    'username' => $username,
                    'chat_id' => $chatId,
                    'chat_type' => $chat_type,
                    'message_id' => $message_id,
                    'message' => $message,
                    'message_type' => $message_type
                ]
            );


            return true;
        } else {

            //Process Inline Query

            //Pluck Values
            $update_id = $data->update_id;
            $user_id = $data->inline_query->from->id;
            $username = $data->inline_query->from->username;
            $chatId = '0';
            $chat_type =  '0';
            $message_id = $data->inline_query->id;
            $message = $data->inline_query->query;
            $message_type = 'inline_query';
            // logger($message_type);

            // Store messages in db

            TelegramBot::create(
                [
                    'update_id' => $update_id,
                    'user_id' => $user_id,
                    'username' => $username,
                    'chat_id' => $chatId,
                    'chat_type' => $chat_type,
                    'message_id' => $message_id,
                    'message' => $message,
                    'message_type' => $message_type
                ]
            );

            return true;
        }
    }

    /**
     * Process anormal text, check if theres a previous command, else send help message
     * @return true Returns true on saving
     */
    public function processNormalMessage()
    {

        //Get Telegram Updates
        $data = Telegram::getWebhookUpdates();

        $previousCommand = $this->previousCommand();

        $command = $previousCommand['message'];

        //Get previous command to process this message
        switch ($command) {
            case '/auth':
                $status = $this->saveTokens();
                break;

            case '/myliked':
                //    $this->saveTokens();
                break;
            default:
                # code...
                break;
        }

        $chatId = $data->message->chat->id;
        $username = $data->message->from->username;

        if (isset($status['action'])) {

            return true;

        } else {

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Hey @' . $username . '!, Reply with /start to learn how to access your Youtube content and autopost or share'
            ]);
            return true;
        }
    }
    /**
     * Gets Previous Command
     * @return array returns an array of the message details
     */
    public function previousCommand()
    {
        $data = Telegram::getWebhookUpdates();

        $user_id = $data->message->from->id;
        $chatId = $data->message->chat->id;


        $command = TelegramBot::select('message', 'message_id', 'status')
            ->where([
                ['user_id', '=', $user_id],
                ['chat_id', '=', $chatId],
                ['message_type', '=', 'bot_command'],
            ])->orderBy('id', 'desc')
            ->first();
        $message = $command->message;
        $message_id = $command->message_id;
        $status = $command->status;
        $commandDetails = array();
        $commandDetails["message"] = $message;
        $commandDetails["message_id"] = $message_id;
        $commandDetails["status"] = $status;
        $commandDetails["chat_id"] = $chatId;
        $commandDetails["user_id"] = $user_id;
        return $commandDetails;
    }
    /**
     * Check Type of Message
     * @return $message_type Returns type of message, either a bot command or a normal text
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
     * Complete Authentication to store  User Tokens
     * @return  array $data Returns data on saving the tokens with array of thre result status either true or false if unable to save, 
     * @return true/false returns true only if no token was sent
     */
    public function saveTokens()
    {

        $message_type = $this->checkMessageType();

        $command = $this->previousCommand();
        $authCommand = Str::contains($command["message"], "/auth");

        //check if previous command was auth and not marked as completed or failed and if message sent is normal text
        if ($authCommand === true && $message_type === "normal_text" && $command['status'] != "completed" && $command['status'] != "failed") {

            if ($this->generateTokens($command) === true) {
                logger("Yeeeaa!!!!");
                $data['status'] = true;
                $data['action'] = true;


                //Success
                Telegram::sendMessage([
                    'chat_id' => $command["chat_id"],
                    'text' => 'Authentication Successful!, Reply with /help for more commands to access your youtube content'
                ]);

                $chatDetails['status'] = "completed";
                $this->updateStatus($chatDetails);
                $this->updateCommand($chatDetails);

                return $data;
            } else {
                logger("Should be false");

                //Error
                Telegram::sendMessage([
                    'chat_id' => $command["chat_id"],
                    'text' => 'Authentication Error, Reply with /auth to grant Telegram Youtube Autopost Bot access'
                ]);

                $chatDetails['status'] = "failed";
                $this->updateStatus($chatDetails);
                $this->updateCommand($chatDetails);
                return false;
            }
        } else {
            return true;
        }
    }
    /**
     * Generate Access Tokens
     *  @return true/false Returns true if tokens are generated successfully, else false
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
     *  @return true/false Return true after updating status, else false
     */
    public function updateStatus(array $userDetails)
    {
        try {
            TelegramBot::where([
                ['user_id', $userDetails['user_id']],
                ['user_id', $userDetails['chat_id']],
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
     * Update Command and mark as Completed, failed, processing etc 
     *  @return true/false Return true after command status, else false
     */
    public function updateCommand(array $userDetails)
    {
        $previousCommand = $this->previousCommand();
        try {
            TelegramBot::where([
                ['user_id', $userDetails['user_id']],
                ['user_id', $userDetails['chat_id']],
                ['message_id', $previousCommand['message_id']]
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
     * @return true/false Return true if user is subscriber, and false if not
     */
    public function isSubscriber($user_id)
    {

        $userExists = Subscribers::where(
            'user_id',
            '=',
            $user_id
        )->exists();

        return $userExists;
    }
}
