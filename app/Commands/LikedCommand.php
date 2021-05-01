<?php

namespace App\Commands;

use Telegram;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use App\YoutubeVideos;
use App\TelegramBot;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\GoogleApiClientController;
use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Keyboard\Keyboard;

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

                $videoList .= sprintf('/%s - %s' . PHP_EOL, $no, $title, $link);
            }
            // $this->replyWithMessage(['text' => $title . ' - ' . $link]);
            $this->replyWithMessage(['text' => $videoList]);
            sleep(3);
            // Reply with the Videos List
        } else {

            //check if user is subscribed to bot updates, if not: added to subcribers table and sent subcription message
            $telegrambot = new TelegramBotController;
            $userExists = $telegrambot->isSubscriber($userDetails['user_id']);

            if ($userExists === false) {

                $this->triggerCommand('subscribe');
            }

            sleep(2);
            $videos = $likedVideos;

            if ($likedVideos['status'] === true) {

                // Reply with the Videos List
                $no = 0;

                $videos = $likedVideos['videos'];
                foreach ($videos as $video) {


                    $link = $video['link'];
                    $title = $video['title'];
                    // echo $link;
                    $no++;

                    //data to be retrieved in callback_query
                    $callbackData =  'url-' . $link;

                    $inlineKeyboard = [
                        [
                            [
                                'text' => 'Click for video Mp3 download',
                                'callback_data' => $callbackData
                            ]
                        ]
                    ];

                    $reply_markup = Keyboard::make([
                        'inline_keyboard' => $inlineKeyboard
                    ]);

                    //reply with video details and option to download mp3
                    $this->replyWithMessage([
                        'text' => $title . ' - ' . $link,
                        'reply_markup' => $reply_markup
                    ]);
                    usleep(800000); //0.8 secs
                }

                // $nextToken = $videos['next'];
                $nextToken = $likedVideos['next'];

                //data to be retrieved in callback_query
                $callbackData =  'nextliked-' . $nextToken;

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
                    'text' => "For More videos: \n tap below to go to the next or previous pages",
                    'reply_markup' => $reply_markup
                ]);

                // $removeCustoKeyboards = Keyboard::remove(
                //     [
                //         'remove_keyboard' => true,
                //     ]
                //     );

                // sleep(3);
                // $this->replyWithMessage([
                //     'text' => 'For More videos: \n tap below to go to the next or previous pages',
                //     'reply_markup' => $removeCustoKeyboards
                // ]);
            } else {

                //user auth tokens has expired or user has not given app access
                $this->replyWithMessage(['text' => 'Ooops, There was an error trying to access the videos, reply with /auth to grant us access to your Youtube Videos']);
            }
            // Trigger another command dynamically from within this command
            // $this->triggerCommand('subscribe');
        }
    }
}
