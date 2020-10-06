<?php

namespace App\Commands;

use App\Subscribers;
use Telegram\Bot\Actions;
use Telegram\Bot\Objects\User;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;

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
    protected $description = "Stop Notifications and Updates from Seleqta Autopost";

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
        $chat_id = $data->chat->id;
        $subscribe = new Subscribers;
        $subscribe->delete(
            [

                'chat_id' => $chat_id
            ]
        );

        //Send Message
        $this->replyWithMessage(['text' => 'Ooops! You have stopped receiving updates from Seleqta Autopost, Goodbye!']);


        // Trigger another command dynamically from within this command
        // $this->triggerCommand('subscribe');
    }
}
