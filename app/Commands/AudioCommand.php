<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use App\Http\Controllers\GoogleApiClientController;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Keyboard\Keyboard;
use App\Http\Controllers\YoutubeDlController;
use Illuminate\Support\Str;

/**
 * Class GetAudioCommand.
 * Download Video Audio in MP3 Format
 * 
 * @author John Muiruri <jontedev@gmail.com>
 */
class AudioCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'getaudio';

    /**
     * @var string Command Description
     */
    protected $description = "Download Video Audio in MP3 Format";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $googleClient = new GoogleApiClientController;


        // Get result from webhook update
        $resultUpdate = $this->getUpdate();

        $username = $resultUpdate->message->from->username;
        $this->replyWithMessage([
            'text' => 'Hello @' .$username .', Please send me the url to the Youtube video.'
        ]);
        

        // if ($regionSet != false) {

        //     //region exists in cache
        //     $userDetails['region'] = Cache::get($chatId);

        //     $trendingVideos =  $googleClient->getTrendingVideos($userDetails);

        //     if ($trendingVideos['status'] === true) {

        //         // Reply with the Videos List
        //         $no = 0;

        //         $videos = $trendingVideos['videos'];
        //         foreach ($videos as $video) {
        //             $link = $video['link'];
        //             $title = $video['title'];
        //             // echo $link;
        //             $no++;

        //             $this->replyWithMessage([
        //                 'text' => $title . ' - ' . $link
        //             ]);
        //             usleep(800000); //0.8 secs
        //         }

        //         $nextToken = $trendingVideos['next'];


        //         //data to be retrieved in callback_query
        //         $callbackData =  'nexttrending-'.$nextToken;


        //         $inlineKeyboard = [
        //             [
        //                 [
        //                     'text' => 'Next Page',
        //                     'callback_data' => $callbackData
        //                 ]
        //             ]
        //         ];

        //         $reply_markup = Keyboard::make([
        //             'inline_keyboard' => $inlineKeyboard
        //         ]);
        //         $this->replyWithMessage([
        //             'text' => 'For More trending Youtube videos:  tap below to go to the next or previous pages',
        //             'reply_markup' => $reply_markup
        //         ]);
        //     } else {

        //         //server error
        //         $this->replyWithMessage(['text' => 'Ooops, There was an error trying to access the videos, try again with /trending command']);
        //     }
        // } else {

        //     //user region not set, has to reply with regions command

        //     $this->replyWithMessage(['text' => 'Ooops, There was an error trying to access the videos, let us set your Country/Region']);
           
        //     // Trigger another command dynamically from within this command
        //     $this->triggerCommand('region');
        // }
    }
}
