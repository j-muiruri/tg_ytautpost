<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\GoogleApiClientController;
use App\Subscribers;
use Telegram\Bot\Keyboard\Keyboard;

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
            sleep(1);

            $tokensRefresh = $link->refreshTokens($userId);
            if ($tokensRefresh === true) {
                $this->replyWithMessage(['text' => 'Authentication Successful!, Reply with /help for more commands to access your youtube content']);
            }
        } else {

            //User has not given us access, generate url and wait for user to send us code

            $authLink = $link->authGoogleApi()->createAuthUrl();
            
            

            $inlineKeyboard = [
                [
                    [
                        'text' => 'Authorize Youtube Account',
                        'callback_data' => $authLink
                    ]
                ]
            ];

            $reply_markup = Keyboard::make([
                'inline_keyboard' => $inlineKeyboard
            ]);

            $this->replyWithMessage([
                'text' => 'Please click on the link following link to grant us access to your Youtube account:',
                'reply_markup' => $reply_markup
            ]);
            sleep(1); //Wait 1 sec

            // Trigger another command dynamically from within this command
            // $this->triggerCommand('subscribe');

            $this->replyWithMessage(['text' => 'After Authorization, Please paste the code received below:']);
        }
    }
}
