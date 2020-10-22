<?php

namespace App\Commands;

use App\Subscribers;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class SubscribersCommand.
 * Subscribe to this bot
 * 
 * @author John Muiruri <jontedev@gmail.com>
 */
class SubscribersCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'subscribe';

    /**
     * @var string Command Description
     */
    protected $description = "Subscribe to Updates or Notifications from Selecta Autopost";

    /**
     * @inheritdoc
     */
    public function handle()
    {

        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        // Get result from webhook update
        $resultUpdate = $this->getUpdate();

        $data = $resultUpdate->message;
        $chatId = $data->chat->id;
        $userId = $data->from->id;
        $username = $data->from->username;
        $firstname = $data->from->first_name;
        // logger($data);

        //Check if user is already subscribed
        $userExists = Subscribers::where('user_id', '=', $userId)->exists();

        if ($userExists === false) {

            //user doesnt exist so create

            Subscribers::create(
                [
                    'user_id' => $userId,
                    'chat_id' => $chatId,
                    'username' => $username,
                    'firstname' => $firstname
                ]
            );



            //Send Message
            $this->replyWithMessage(['text' => 'Hello!  Welcome ' . $firstname . ',  You have succesfully subscribed to Seleqta Youtube Autopost!']);
            // } else {
            //     exit;
            // }
            // Trigger another command dynamically from within this command
            // $this->triggerCommand('subscribe');
        } else {

            $this->replyWithMessage(['text' => 'Hello! ' . $firstname . ',  You have already subscribed to Seleqta Youtube Autopost!']);
        }
    }
}
