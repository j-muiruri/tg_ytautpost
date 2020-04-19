<?php

namespace App\Commands;

use App\Subscribers;
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
    public function handle($arguments)
    {

        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);


        $uid = User::getId();

        $user = User::getUsername();
        //check if user is subscribed to bot updates

        $newUser = Subscribers::where('user_id', '=', $uid)->first();

        if ($newUser === null) {

            //user doesnt exist so create

            Subscribers::create(
                [
                    'user_id' => $uid,
                    'username' => $user
                ]
            );



            //Send Message
            $this->replyWithMessage(['text' => 'Great! You have been added to the Selecta Autopost Subscribers List']);
        } else {
            exit;
        }
        // Trigger another command dynamically from within this command
        // $this->triggerCommand('subscribe');
    }
}
