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
    public function handle()
    {
        //Send Message
        $this->replyWithMessage(['text' => 'Great! Seleqta Autopost has found the following Channel subscriptions:']);
        // sleep(1);
        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        sleep(2);
        $channels = MySubscriptions::paginate(20);

        // Reply with the Videos List

        $no = 0;
        // Get result from webhook update
        $resultUpdate = $this->getUpdate();
        //  Log::debug($resultUpdate);
        $type = $resultUpdate->message->chat->type;

        if ($type === 'supergroup') {
            $videoList = "";
            foreach ($channels as $ch) {
                $link = $ch['link'];
                $title = $ch['title'];
                // echo $link;
                $no++;

                $videoList .= sprintf('%s. %s - https://youtube.com/channel/%s' . PHP_EOL, $no, $title, $link);
                // $this->replyWithMessage(['text' => $no . '. ' . $title . ' -  https://youtube.com/channel/' . $link]);

                // Trigger another command dynamically from within this command
                // $this->triggerCommand('subscribe');
            }

            $this->replyWithMessage(['text' => $videoList]);
        sleep(3);
        } else {
            $videoList = "";
            foreach ($channels as $ch) {
                $link = $ch['link'];
                $title = $ch['title'];
                // echo $link;
                $no++;

                // $videoLi.= sprintf('%s. %s - https://youtube.com/channel/%s' . PHP_EOL, $no, $title, $link);
                $this->replyWithMessage(['text' => $no . '. ' . $title . ' -  https://youtube.com/channel/' . $link]);
            }
        }
    }
}
