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

        //Send Message
        $this->replyWithMessage(['text' => 'Choose your preffered country below']); // Trending Videos from Youtube:
        sleep(1);

        $regionData =  $googleClient->getRegions();

        // if ($type === 'supergroup') {

        //     sleep(3);
        //     // Reply with the Videos List
        // } else {

        logger($regionData);

        if ($regionData['status'] === true) {

            // Reply with the Videos List
            $no = 0;

            $regions = $regionData['regions'];
            foreach ($regions as $region) {

                logger($region['name']);
                $region = $region['region'];
                $name[0] =$region['name'];

                logger($name);
                $keyboardButtons[] = [
                    'text' => $name[0],
                    'callback_data' => $region
                ];

                $no++;
            }

            $inlineKeyboard = [
                [
                    [
                        $keyboardButtons
                    ]
                ]
            ];

            $reply_markup = Keyboard::make([
                'inline_keyboard' => $inlineKeyboard
            ]);

            $this->replyWithMessage([
                'text' => 'Here is the list of Available Regions/Countries: ',
                'reply_markup' => $reply_markup
            ]);
            // } else {

            //     //user auth tokens has expired or user has not given app access
            //     $this->replyWithMessage(['text' => 'Ooops, There was an error trying to access the videos, reply with /auth to grant us access to your Youtube Videos']);
            // }
            // Trigger another command dynamically from within this command
            // $this->triggerCommand('subscribe');
        }
    }
}
