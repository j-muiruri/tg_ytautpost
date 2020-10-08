<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\GoogleApiClientController;

/**
 * Class AuthCommand.
 * Authorize bot to access youtube account
 * 
 * @author John Muiruri <jontedev@gmail.com>
 */
class AuthCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'auth';

    /**
     * @var string Command Description
     */
    protected $description = "Authorize Bot to Read Your YouTube Videos";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        //Send Message
        $this->replyWithMessage(['text' => 'Please click on the link following link to allow Sign in to your Youtube account:']);
        sleep(2);
        $link = new GoogleApiClientController;

        $authLink = $link->getAuthGoogleApi();
        // Get result from webhook update
        $resultUpdate = $this->getUpdate();
        Log::debug($resultUpdate);


        $this->replyWithMessage(['text' => $authLink]);
        usleep(800000); //1.5 secs

        // Trigger another command dynamically from within this command
        // $this->triggerCommand('subscribe');
    }
}
