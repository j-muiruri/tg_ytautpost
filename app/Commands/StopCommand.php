<?php

namespace App\Commands;

use App\Subscribers;
use Telegram\Bot\Actions;
use Telegram\Bot\Objects\User;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use App\Http\Controllers\GoogleApiClientController;

/**
 * Class StopCommand.
 * Stop receiving Content/updates from this bot
 * 
 * @author John Muiruri <jontedev@gmail.com>
 */
class StopCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'stop';

    /**
     * @var string Command Description
     */
    protected $description = "Stop Notifications and Updates from Seleqta Autopost, Revoke access to your Youtube videos";

    /**
     * @inheritdoc
     */
    public function handle()
    {

        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        // Get result from webhook update
        $resultUpdate = $this->getUpdate();

        $client = new GoogleApiClientController;
        $data = $resultUpdate->message;
        $userDetails['user_id'] = $data->from->id;
        $userDetails['chat_id'] = $data->chat->id;
        $client->revokeAccess($userDetails);

        //Send Message
        $this->replyWithMessage([
            'text' =>
            'Ooops! You: \n 
            1. have revoked my access to your Youtube videos \n
            2. will stop receiving updates from Seleqta Youtube Autopost. \n
            Goodbye @$username!'
        ]);


        // Trigger another command dynamically from within this command
        // $this->triggerCommand('subscribe');
    }
}
