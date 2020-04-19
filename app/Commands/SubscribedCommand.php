<?php

namespace App\Commands;

use App\MySubscriptions;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use App\YoutubeVideos;
use App\TelegramBot;
/**
 * Class LikedCommand.
 * Get list of liked videos
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
    protected $description = "List Channels I have Subscribed to";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        //Send Message
        $this->replyWithMessage(['text' => 'Great! Selecta Autopost has found the following Channel subscriptions:']);

        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $channels = MySubscriptions::paginate(10);

        // Reply with the Videos List

        $no = 0;

        foreach ($channels as $ch) {

            $link = $ch['link'];
            $title = $ch['title'];
            // echo $link;
            $no++;

            $this->replyWithMessage(['text' => $no . '. ' . $title . ' -  https://youtube.com/channel/' . $link]);
        }

        // Trigger another command dynamically from within this command
        // $this->triggerCommand('subscribe');
    }
}
