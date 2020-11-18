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
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Keyboard\Keyboard;

/**
 * Class TrendingCommand.
 * List Trending or Popular Videos by Country/Region
 * 
 * @author John Muiruri <jontedev@gmail.com>
 */
class TrendingCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'trending';

    /**
     * @var string Command Description
     */
    protected $description = "List Trending or Popular Videos by Country";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $googleClient = new GoogleApiClientController;


        // Get result from webhook update
        $resultUpdate = $this->getUpdate();
        $userDetails['user_id'] = $resultUpdate->message->from->id;
        $chatId = $resultUpdate->message->chat->id;

        $regionSet =  Cache::has($chatId);

        if ($regionSet != false) {

            //region exists in cache
            $userDetails['region'] = Cache::get($chatId);

            $trendingVideos =  $googleClient->getTrendingVideos($userDetails);

            if ($trendingVideos['status'] === true) {

                // Reply with the Videos List
                $no = 0;

                $videos = $trendingVideos['videos'];
                foreach ($videos as $video) {
                    $link = $video['link'];
                    $title = $video['title'];
                    // echo $link;
                    $no++;

                    $this->replyWithMessage([
                        'text' => $no . '. ' . $title . ' - ' . $link
                    ]);
                    usleep(800000); //0.8 secs
                }

                $nextToken = $trendingVideos['next'];


                //data to be retrieved in callback_query
                $callbackData =  array(
                    'action' => 'nexttrending',
                    'data' => $nextToken
                );


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
                    'text' => 'For More trending Youtube videos:  tap below to go to the next or previous pages',
                    'reply_markup' => $reply_markup
                ]);
            } else {

                //server error
                $this->replyWithMessage(['text' => 'Ooops, There was an error trying to access the videos, try again with /trending command']);
            }
        } else {

            //user region not set, has to reply with regions command
            $this->replyWithMessage(['text' => 'Ooops, There was an error trying to access the videos, to set your Country/Region reply with the Set Region/Country Command: \n /region']);
            // Trigger another command dynamically from within this command
            // $this->triggerCommand('region');
        }
    }
}
