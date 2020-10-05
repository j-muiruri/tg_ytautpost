<?php

namespace App\Commands;

use App\Subscribers;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Actions;
use Telegram\Bot\Objects\User;
use Telegram\Bot\Commands\Command;

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
    protected $description = "Subscribe to Udates or Notifications from Selecta Autopost";

    /**
     * @inheritdoc
     */
    public function handle()
    {

        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);


        // $user_id = $this->id;
        // $user_id = $this->chat_id;
        // $user_id = explode(',', $this);
        $user_id = Telegram::getUpdates('id');

        // $newUser = Subscribers::where('chat_id', '=', $uid)->first();

        // if ($newUser === null) {

        //     //user doesnt exist so create

        //     Subscribers::create(
        //         [
        //             'chat_id' => $uid
        //         ]
        //     );



            //Send Message
            $this->replyWithMessage(['text' => 'Great! User:'.$user_id.'You have been added to the Selecta Autopost Subscribers List']);
        // } else {
        //     exit;
        // }
        // Trigger another command dynamically from within this command
        // $this->triggerCommand('subscribe');
    }
}
