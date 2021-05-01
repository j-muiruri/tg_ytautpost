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
            'text' => 'Hello @' .$username .', Please send me the url/link to the Youtube video.'
        ]);
        

       
        //     // Trigger another command dynamically from within this command
        //     $this->triggerCommand('region');
        // }
    }
}
