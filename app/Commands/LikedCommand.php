<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use App\YoutubeVideos;
use App\TelegramBot;
use Illuminate\Pagination\Paginator;

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
    public function handle()
    {
        //Send Message
        $this->replyWithMessage(['text' => 'Great! Seleqta Autopost has found the following videos:']);
        sleep(5);
        // // This will update the chat status to typing...
        // $this->replyWithChatAction(['action' => Actions::TYPING]);
        // sleep(1);
        $videos = YoutubeVideos::orderBy('id', 'desc')->paginate(10);

        // Reply with the Videos List

        $no = 0;

        foreach ($videos as $video) {

            $link = $video['link'];
            $title = $video['title'];
            // echo $link;
            $no++;

            $this->replyWithMessage(['text' => $no.'. '.$title.' - '.$link]);
            usleep(1500000); //1.5 secs
        }
        // send next page link

        $arrResult = $videos->toArray();
        // $this->replyWithMessage(['text' =>$arrResult['next_page_url']]);
        // Trigger another command dynamically from within this command
        // $this->triggerCommand('subscribe');
    }
}
