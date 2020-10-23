<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use App\YoutubeVideos;
use App\TelegramBot;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\GoogleApiClientController;

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
        $this->replyWithMessage(['text' => 'Great! Seleqta Autopost Youtube Bot has found the following Liked Videos from Youtube:']);
        sleep(2);

        // Get result from webhook update
        $resultUpdate = $this->getUpdate();
        $type = $resultUpdate->message->chat->type;
        $userDetails['user_id'] = $resultUpdate->message->from->id;

        $googleClient = new GoogleApiClientController;

        $likedVideos =  $googleClient->getLikedVideos($userDetails);

        if ($type === 'supergroup') {
            $videos = YoutubeVideos::orderBy('id', 'desc')->paginate(20);

            $no = 0;
            $videoList = "";
            foreach ($videos as $video) {
                $link = $video['link'];
                $title = $video['title'];
                // echo $link;
                $no++;

                $videoList .= sprintf('/%s. %s - %s' . PHP_EOL, $no, $title, $link);
            }
            // $this->replyWithMessage(['text' => $no . '. ' . $title . ' - ' . $link]);
            $this->replyWithMessage(['text' => $videoList]);
            sleep(3);
            // Reply with the Videos List
        } else {

            $videos = $likedVideos;
            // logger($videos);
            // logger($videos);
            if ($likedVideos['status'] === true) {

                // Reply with the Videos List
                $no = 0;

                $videos = $likedVideos['videos'];
                foreach ($videos as $video) {

                   
                    $link = $video['link'];
                    $title = $video['title'];
                    // echo $link;
                    $no++;

                    $this->replyWithMessage(['text' => $no . '. ' . $title . ' - ' . $link]);
                    usleep(800000); //0.8 secs
                }
            } else {

                //user auth tokens has expired or user has not given app access
                $this->replyWithMessage(['text' => 'Ooops, There was an error trying to access the videos, reply with /auth to grant us access to your Youtube Videos']);
            }
            // Trigger another command dynamically from within this command
            // $this->triggerCommand('subscribe');
        }
    }
}
