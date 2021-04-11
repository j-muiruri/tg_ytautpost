<?php

namespace App\Jobs;

use App\Http\Controllers\TelegramBotController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\YoutubeDlController;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;

class AudioDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * url to video
     * @var string
     **/
    public $url;

    /**
     * Telegram chat_id
     * @var int
     **/
    public $chatId;

    /**
     * Telegram user_id
     * @var int
     **/
    public  $userId;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $chatId, $userId)
    {
        $this->url = $url;
        $this->chatId = $chatId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        logger("Job has been called");
        try {

            $youtubeDl = new YoutubeDlController;
            $telegram = new TelegramBotController;

            $fileDetails = $youtubeDl->downloadUserAudio($this->url);

            if ($fileDetails['status'] == true) {

                logger($fileDetails['audio']);
                foreach ($fileDetails['audio'] as $audio) {
                    
                Telegram::sendAudio([
                    'chat_id' => $this->chatId,
                    'audio' => InputFile::create($audio['location'], $audio['name']),
                    'title' => $audio['name'],
                    'caption' => 'Made by Youtube Bot by @jontelov'
                ]);

                # code...
            }
                $chatDetails['status'] = "completed";
                $chatDetails['user_id'] = $this->userId;
                $chatDetails['chat_id'] = $this->chatId;
                $telegram->updateStatus($chatDetails);
                $telegram->updateCommand($chatDetails);
                return true;
            } else {
                Telegram::sendMessage([
                    'chat_id' => $this->chatId,
                    'text' => 'Ooops, an error occured while fetching the audio, please try again'
                ]);
                return false;
            }
        } catch (\Throwable $th) {

            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => 'Ooops, an error occured while fetching the audio, please note the audio should be not be more than a duration of 10:00 minutes'
            ]);

            $chatDetails['status'] = "completed";
            $chatDetails['user_id'] = $this->userId;
            $chatDetails['chat_id'] = $this->chatId;
            $telegram->updateStatus($chatDetails);
            $telegram->updateCommand($chatDetails);
            throw $th;
            return false;
        }
    }
}
