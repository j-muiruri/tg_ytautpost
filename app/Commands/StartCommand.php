<?php
namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class StartCommand.
 * 
 * @author John Muiruri <jontedev@gmail.com>
 */
class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'start';

    /**
     * @var string Command Description
     */
    protected $description = "Start Command to get you started";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        // This will send a message using `sendMessage` method behind the scenes to
        // the user/chat id who triggered this command.

         // Trigger another command dynamically from within this command
        // When you want to chain multiple commands within one or process the request further.
        // The method supports second parameter arguments which you can optionally pass, By default
        // it'll pass the same arguments that are received for this command originally.

        //check if user is subscribed to bot updates, if not: added to subcribers table and sent subcription messgae
        // $this->triggerCommand('subscribe');

         // Get result from webhook update
         $resultUpdate = $this->getUpdate();
        //  Log::debug($resultUpdate);

        // `replyWith<Message|Photo|Audio|Video|Voice|Document|Sticker|Location|ChatAction>()` all the available methods are dynamically
        // handled when you replace `send<Method>` with `replyWith` and use the same parameters - except chat_id does NOT need to be included in the array.
        $this->replyWithMessage(['text' => 'Seleqta Autopost Bot available commands:']);

        sleep(3);
        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        sleep(3);
        // This will prepare a list of available commands and send the user.
        // First, Get an array of all registered commands
        // They'll be in 'command-name' => 'Command Handler Class' format.
        $commands = $this->getTelegram()->getCommands();

        // Build the list
        $response = '';
        foreach ($commands as $name => $command) {
            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
            sleep(3);
        }

        // Reply with the commands list
        $this->replyWithMessage(['text' => $response]);

        sleep(3);
        
    }
}
