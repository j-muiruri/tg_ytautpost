<?php

namespace App\Commands;

use App\Http\Controllers\GoogleApiClientController;
use App\MySubscriptions;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use App\YoutubeVideos;
use App\TelegramBot;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Class SubscribedCommand.
 * Get list of User Subscriptions
 * 
 * @author John Muiruri <jontedev@gmail.com>
 */
class SubscribedCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'subscriptions';

    /**
     * @var string Command Description
     */
    protected $description = "List  My Channel Subscriptions";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        //Send Message
        $this->replyWithMessage(['text' => 'Great! Seleqta Autopost has found the following Channel subscriptions:']);

        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $channels = MySubscriptions::paginate(20);

        // Get result from webhook update
        $resultUpdate = $this->getUpdate();
        $type = $resultUpdate->message->chat->type;
        $userDetails['user_id'] = $resultUpdate->message->from->id;

        $googleClient = new GoogleApiClientController;

        $userSubscriptions =  $googleClient->getUserSubscriptions($userDetails);

        $no = 0;


        if ($type === 'supergroup') {
            $subscriptionList = "";
            foreach ($channels as $ch) {
                $link = $ch['link'];
                $title = $ch['title'];
                // echo $link;
                $no++;

                $subscriptionList .= sprintf('%s. %s - https://youtube.com/channel/%s' . PHP_EOL, $no, $title, $link);
                // $this->replyWithMessage(['text' => $no . '. ' . $title . ' -  https://youtube.com/channel/' . $link]);

                // Trigger another command dynamically from within this command
                // $this->triggerCommand('subscribe');
            }

            $this->replyWithMessage(['text' => $subscriptionList]);
            sleep(3);
        } else {

            $subscriptions = $userSubscriptions;
            // logger($subscriptions);
            // logger($subscriptions);
            if ($userSubscriptions['status'] === true) {

                // Reply with the Subscription Channel List
                $no = 0;

                $subscriptions = $userSubscriptions['subscriptions'];
                foreach ($subscriptions as $subscription) {


                    $link = $subscription['link'];
                    $title = $subscription['title'];
                    // echo $link;
                    $no++;

                    $this->replyWithMessage(['text' => $no . '. ' . $title . ' - ' . $link]);
                    usleep(800000); //0.8 secs
                }

                // $nextToken = $subscriptions['next'];
                $nextToken = $userSubscriptions['next'];

                //data to be retrieved in callback_query
                $callbackData =  'nextsubscriptions-'.$nextToken;

                $inlineKeyboard = [
                    [
                        [
                            'text' => 'Next Page',
                            'callback_data' => $callbackData
                        ]
                    ]
                ];

                $reply_markup = Keyboard::make([
                    'inline_keyboard' => $inlineKeyboard
                ]);

                $this->replyWithMessage([
                    'text' => 'For More Subscriptions: \n tap below to go to the next or previous pages',
                    'reply_markup' => $reply_markup
                ]);

                // $removeCustoKeyboards = Keyboard::remove(
                //     [
                //         'remove_keyboard' => true,
                //     ]
                //     );

                // sleep(3);
                // $this->replyWithMessage([
                //     'text' => 'For More Subscriptions: \n tap below to go to the next or previous pages',
                //     'reply_markup' => $removeCustoKeyboards
                // ]);
            } else {

                //user auth tokens has expired or user has not given app access
                $this->replyWithMessage(['text' => 'Ooops, There was an error trying to access the Subscriptions, reply with /auth to grant us access to your Youtube Videos']);
            }
            // Trigger another command dynamically from within this command
            // $this->triggerCommand('subscribe');
        }
    }
}
