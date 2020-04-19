<?php

namespace App\Commands;

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
class LikedCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'myliked';

    /**
     * @var string Command Description
     */
    protected $description = "List My Liked Videos";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        //Send Message
        $this->replyWithMessage(['text' => 'Great! Selecta Autopost has found the following videos:']);

        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $videos = YoutubeVideos::paginate(10);

        // Reply with the Videos List

        $no = 0;

        foreach ($videos as $video) {

            $link = $video['link'];
            $title = $video['title'];
            // echo $link;
            $no++;

            $this->replyWithMessage(['text' => $no.'. '.$title.' - '.$link]);
        }

        // Trigger another command dynamically from within this command
        // $this->triggerCommand('subscribe');
    }
}
