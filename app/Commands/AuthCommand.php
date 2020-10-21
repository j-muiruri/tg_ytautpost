<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\GoogleApiClientController;
use App\Subscribers;

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

        $link = new GoogleApiClientController;
        // Get result from webhook update
        $resultUpdate = $this->getUpdate();

        Log::debug($resultUpdate);
        
        $userId = $resultUpdate->message->from->id;
        $tokenExists = Subscribers::where(
            'user_id',
            '=',
            $userId
        )
            ->whereNotNull('access_tokens')
            ->exists();

            //Check if user has already give us access to Yt acc
        if ($tokenExists === true) {

            //Send Message
            $this->replyWithMessage(['text' => 'You have already given me access  to your Youtube Account! Kindly wait as I check on the permissions']);
            sleep(2);

            $tokensRefresh = $link->refreshTokens($userId);
            if ($tokensRefresh === true) {
                $this->replyWithMessage(['text' => 'Authentication Successful!, Reply with /help for more commands to access your youtube content']);
            }
        } else {

            //User has not given us access, generate url and wait for user to send us code

            //Send Message
            $this->replyWithMessage(['text' => 'Please click on the link following link to grant us access to your Youtube account:']);
            sleep(2);


            $authLink = $link->authGoogleApi()->createAuthUrl();
            
            


            $this->replyWithMessage(['text' => $authLink]);
            sleep(2); //Wait 1 sec

            // Trigger another command dynamically from within this command
            // $this->triggerCommand('subscribe');

            $this->replyWithMessage(['text' => 'After Authorization, Please paste the code recieved below:']);
        }
    }
}
